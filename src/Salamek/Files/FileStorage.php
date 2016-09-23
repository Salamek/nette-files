<?php

namespace Salamek\Files;

use Nette;
use Nette\Http\FileUpload;
use Nette\Application\Responses\FileResponse;


/**
 * Class ImageStorage
 * @package Salamek\Files
 */
class FileStorage extends Nette\Object
{

    /** @var string */
    private $dataDir;

    /** @var IStructureRepository */
    private $structureRepository;

    /** @var IFileRepository  */
    private $fileRepository;

    /** @var IStructureFileRepository  */
    private $structureFileRepository;

    /** @var array */
    public $onUploadFile = [];

    private $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    private $textExtensions = ['txt', 'css', 'csv', 'html', 'log', 'sql', 'xhtml', 'xml'];
    private $mediaExtensions = ['ac3', 'avi', 'fla', 'flv', 'm4a', 'mid', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'ogg', 'wav', 'webm', 'wma'];
    

    /**
     * FileStorage constructor.
     * @param $dir
     * @param IStructureRepository $structureRepository
     * @param IFileRepository $fileRepository
     * @param IStructureFileRepository $structureFileRepository
     */
    public function __construct($dir, IStructureRepository $structureRepository, IFileRepository $fileRepository, IStructureFileRepository $structureFileRepository)
    {
        $this->setDataDir($dir);
        $this->structureRepository = $structureRepository;
        $this->fileRepository = $fileRepository;
        $this->structureFileRepository = $structureFileRepository;
    }


    /**
     * @param $dir
     */
    public function setDataDir($dir)
    {
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
        $this->dataDir = $dir;
    }


    /**
     * @param $filePath
     * @param $type
     * @return bool
     * @throws \Exception
     */
    public function isFileType($filePath, $type = IFile::TYPE_IMAGE)
    {
        $info = pathinfo($filePath);
        $testArray = [];
        switch ($type) {
            case IFile::TYPE_IMAGE:
                $testArray = $this->imageExtensions;
                break;
            case IFile::TYPE_MEDIA:
                $testArray = $this->mediaExtensions;
                break;
            case IFile::TYPE_TEXT:
                $testArray = $this->textExtensions;
                break;
            case IFile::TYPE_BINARY:
                if (
                    !$this->isFileType($filePath, IFile::TYPE_IMAGE) &&
                    !$this->isFileType($filePath, IFile::TYPE_MEDIA) &&
                    !$this->isFileType($filePath, IFile::TYPE_TEXT)
                ) {
                    return true;
                }
                break;

            default:
                throw new \Exception('Unsupported format');
                break;
        }

        if (array_key_exists('extension', $info) && in_array($info['extension'], $testArray)) {
            return true;
        }

        //Mime type testing
        if (function_exists('finfo_open')) {
            $mime = $this->getMimeType($filePath);

            switch ($type) {
                case IFile::TYPE_IMAGE:
                    return strpos($mime, 'image') !== false;
                    break;
                case IFile::TYPE_MEDIA:
                    return (strpos($mime, 'video') !== false || strpos($mime, 'audio') !== false);
                    break;
                case IFile::TYPE_TEXT:
                    return (strpos($mime, 'text') !== false || strpos($mime, 'plain') !== false);
                    break;
                case IFile::TYPE_BINARY:
                    if (
                        !$this->isFileType($filePath, IFile::TYPE_IMAGE) &&
                        !$this->isFileType($filePath, IFile::TYPE_MEDIA) &&
                        !$this->isFileType($filePath, IFile::TYPE_TEXT)
                    ) {
                        return true;
                    }
                    break;

                default:
                    throw new \Exception('Unsupported format');
                    break;
            }
        }

        return false;
    }

    /**
     * @param IStructure $structure
     * @return array
     * @throws \Exception
     */
    public function getStructureFilesInfo(IStructure $structure)
    {
        $data = [
            'files' => 0,
            'folders' => 0,
            'size' => 0,
            'tree' => []
        ];

        $structureFiles = $structure->getStructureFiles();
        //Get files count
        $data['files'] += $structureFiles->count();
        //Get files size

        $data['tree'][$structure->getId()]['directory'] = $structure->getId();
        foreach ($structureFiles AS $structureFile) {
            $data['size'] += $structureFile->getFile()->getSize();
            $data['tree'][$structure->getId()]['files'][] = $structureFile->getId();
        }


        $childs = $structure->getChildren();
        $data['folders'] += $childs->count();
        //Go recursion
        foreach ($childs AS $child) {
            $subData = $this->getStructureFilesInfo($child);
            $data['files'] += $subData['files'];
            $data['folders'] += $subData['folders'];
            $data['size'] += $subData['size'];
            $data['tree'][$structure->getId()]['directories'] = $subData['tree'];
        }
        return $data;
    }

    /**
     * @param $filePath
     * @return mixed|null
     * @throws \Exception
     */
    public function detectType($filePath)
    {
        $types = [];
        $types[] = IFile::TYPE_IMAGE;
        $types[] = IFile::TYPE_MEDIA;
        $types[] = IFile::TYPE_TEXT;
        $types[] = IFile::TYPE_BINARY;
        foreach ($types AS $type) {
            if ($this->isFileType($filePath, $type)) {
                return $type;
                break;
            }
        }
        return null;
    }


    /**
     * @param \SplFileInfo|FileUpload $info
     * @param null|IStructure $structure
     * @return mixed
     * @throws \Exception
     */
    public function processFile($info, IStructure $structure = null)
    {
        if ($info instanceof \SplFileInfo) {
            $file = $info->getRealPath();
            $name = $info->getBasename('.' . $info->getExtension());
            $upload = false;
        } elseif ($info instanceof FileUpload) {
            $file = $info->getTemporaryFile();
            $name = pathinfo($info->getSanitizedName(), PATHINFO_FILENAME);
            $upload = true;
        } else {
            throw new \Exception('Unknow info');
        }

        $md5 = md5_file($file);

        if ($this->fileRepository->isSumFree($md5)) {
            if ($upload) {
                $extension = strtolower(pathinfo($info->getSanitizedName(), PATHINFO_EXTENSION));
                $mimeType = $info->getContentType();
            } else {
                $extension = strtolower($info->getExtension());
                $mimeType = $this->getMimeType($file);
            }

            if (!$extension) {
                throw new \Exception('Failed to detect extension!');
            }

            $newFile = $this->fileRepository->createNewFile($md5, $info->getSize(), $extension, $mimeType, $this->detectType($file));
            
            if ($upload) {
                $result = $info->move($this->dataDir . '/' . $newFile->getSum(). '.' . $newFile->getExtension());
            } else {
                $result = copy($file, $this->dataDir . '/' . $newFile->getSum() . '.' . $newFile->getExtension());
            }

            if (!$result) {
                throw new \Exception('Failed to save file');
            }
        } else {
            if ($upload) {
                unlink($file);
            }
            $newFile = $this->fileRepository->getOneBySum($md5);
        }


        if ($upload) {
            $cnt = 0;

            $insertName = $name;
            do {
                $insertName = $name . ($cnt ? $cnt : '');
                $cnt++;
            } while (!$this->structureFileRepository->isNameFree($insertName, $structure));

            $structureFile = $this->structureFileRepository->createNewStructureFile($insertName, $newFile, $structure);
        } else {
            $structureFile = $this->structureFileRepository->getOneByNameAndStructure($name, $structure);

            $foundMd5Name = $this->fileRepository->getOneBySum($name);
            if (!$structureFile && !$foundMd5Name) {
                $structureFile = $this->structureFileRepository->createNewStructureFile($name, $newFile, $structure);
            }
        }

        $this->onUploadFile($structureFile);

        return $structureFile;
    }


    /**
     * @param $filePath
     * @return mixed
     */
    public function getMimeType($filePath)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $mime = finfo_file($finfo, $filePath);

            finfo_close($finfo);
            return $mime;
        }
        return null;
    }

    /**
     * @param $zipArchive
     * @param $structureTree
     * @param string $path
     * @throws \Exception
     */
    private function structureToZip($zipArchive, $structureTree, $path = '')
    {
        foreach ($structureTree AS $k => $v) {
            if (array_key_exists('directory', $v)) {
                $structure = $this->structureRepository->get($v['directory']);
                $path .= '/' . $structure->name;
            }


            if (array_key_exists('files', $v)) {
                foreach ($v['files'] AS $fileId) {
                    $file = $this->structureFileRepository->get($fileId);
                    $filePath = $this->dataDir . '/' . $file->filesFiles->sum . '.' . $file->filesFiles->extension;

                    if (!is_file($filePath)) {
                        throw new \Exception('File for pack not found');
                    }
                    $zipArchive->addFile($filePath, $path . '/' . $file->name . '.' . $file->filesFiles->extension);
                }
            }

            if (array_key_exists('directories', $v)) {
                $this->structureToZip($zipArchive, $v['directories'], $path);
            }
        }
    }

    /**
     * @param IStructureFile $structureFile
     * @return FileResponse
     */
    public function downloadFile(IStructureFile $structureFile)
    {
        $path = $this->dataDir . '/' . $structureFile->getFile()->getBasename();
        return new FileResponse($path, $structureFile->getName() . '.' . $structureFile->getFile()->getExtension(), $structureFile->getFile()->getMimeType());
    }

    /**
     * @param IStructure $structure
     * @return FileResponse
     * @throws \Exception
     */
    public function downloadStructure(IStructure $structure)
    {
        $info = $this->getStructureFilesInfo($structure);


        $zipArchive = new \ZipArchive();
        $tmpfname = tempnam(sys_get_temp_dir(), 'structure-download');

        if (!$zipArchive->open($tmpfname, \ZipArchive::CREATE)) {
            throw new \Exception('Failed to create ZIP archive');
        }

        $this->structureToZip($zipArchive, $info['tree']);

        $zipArchive->close();


        return new FileResponse($tmpfname, $structure->getName() . '.zip', 'application/zip');
    }

    /**
     * @param IFile $file
     */
    private function deleteFile(IFile $file)
    {
        $this->fileRepository->deleteFile($file);
    }
    
    public function deleteStructureFile(IStructureFile $IStructureFile)
    {
        $this->structureFileRepository->deleteStructureFile($IStructureFile);
    }

    public function deleteStructure(IStructure $structure)
    {
        $this->structureRepository->deleteStructure($structure);
    }
    
    /**
     * @return string
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

}


/**
 * Class FileNotFoundException
 * @package Salamek\Files
 */
class FileNotFoundException extends \RuntimeException
{

}