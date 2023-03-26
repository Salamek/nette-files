<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files;

use Nette\Http\FileUpload;
use Nette\Application\Responses\FileResponse;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Image as NImage;
use Salamek\Files\Models\IFile;
use Salamek\Files\Models\IFileRepository;
use Salamek\Files\Models\IStructure;
use Salamek\Files\Models\IStructureFile;
use Salamek\Files\Models\IStructureFileRepository;
use Salamek\Files\Models\IStructureRepository;

/**
 * Class ImageStorage
 * @package Salamek\Files
 */
class FileStorage
{
    use SmartObject;

    const ICON = 'ico';
    const ICON_DARK = 'ico_dark';

    /** @var string */
    private $dataDir;

    /** @var string */
    private $iconDir;

    /** @var string */
    private $webTempDir;

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

    private $iconsSupported = [
        'ac3',
        'accdb',
        'ade',
        'adp',
        'ai',
        'aiff',
        'avi',
        'bmp',
        'css',
        'csv',
        'dmg',
        'doc',
        'docx',
        'fla',
        'flv',
        'gif',
        'gz',
        'html',
        'iso',
        'jpeg',
        'jpg',
        'log',
        'm4a',
        'mdb',
        'mid',
        'mov',
        'mp3',
        'mp4',
        'mpeg',
        'mpg',
        'odb',
        'odf',
        'odg',
        'odp',
        'ods',
        'odt',
        'ogg',
        'otg',
        'otp',
        'ots',
        'ott',
        'pdf',
        'png',
        'ppt',
        'pptx',
        'psd',
        'rar',
        'rtf',
        'sql',
        'svg',
        'tar',
        'tiff',
        'txt',
        'wav',
        'webm',
        'wma',
        'xhtml',
        'xls',
        'xlsx',
        'xml',
        'zip',
        'folder',
        'folder_back'
    ];

    /**
     * FileStorage constructor.
     * @param string $dir
     * @param string $iconDir
     * @param string $webTempDir
     * @param IStructureRepository $structureRepository
     * @param IFileRepository $fileRepository
     * @param IStructureFileRepository $structureFileRepository
     */
    public function __construct(
        string $dir,
        string $iconDir,
        string $webTempDir,
        IStructureRepository $structureRepository,
        IFileRepository $fileRepository,
        IStructureFileRepository $structureFileRepository
    )
    {
        $this->setDataDir($dir);
        $this->setIconDir($iconDir);
        $this->setWebTempDir($webTempDir);
        $this->structureRepository = $structureRepository;
        $this->fileRepository = $fileRepository;
        $this->structureFileRepository = $structureFileRepository;
    }


    /**
     * @param string $dir
     */
    public function setDataDir(string $dir): void
    {
        if (!is_dir($dir)) {
           Tools::mkdir($dir);
        }
        $this->dataDir = $dir;
    }

    /**
     * @param string $iconDir
     */
    public function setIconDir(string $iconDir): void
    {
        if (!is_dir($iconDir)) {
            Tools::mkdir($iconDir);
        }
        $this->iconDir = $iconDir;
    }

    /**
     * @param string $webTempDir
     */
    public function setWebTempDir(string $webTempDir): void
    {
        if (!is_dir($webTempDir)) {
            Tools::mkdir($webTempDir);
        }
        $this->webTempDir = $webTempDir;
    }

    /**
     * @return string
     */
    public function getIconDir(): string
    {
        return $this->iconDir;
    }

    /**
     * @return string
     */
    public function getWebTempDir(): string
    {
        return $this->webTempDir;
    }

    /**
     * @param $filePath
     * @param $type
     * @return bool
     * @throws \Exception
     */
    public function isFileType(string $filePath, $type = IFile::TYPE_IMAGE): bool
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
        $mime = $this->getMimeType($filePath);
        if (!is_null($mime)) {

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
    public function getStructureFilesInfo(IStructure $structure): array
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
     * @param string $filePath
     * @return string|null
     * @throws \Exception
     */
    public function detectType(string $filePath): ?string
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
     * Save file into storage
     * @param $info SplFileInfo or FileUpload
     * @returns IFile
     */
    public function saveFile($info): IFile
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
            throw new \Exception('Unknown info');
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

            $fileSystemPath = $this->getFileSystemPath($newFile);

            $dataDirForFile = $this->getDataDirForFile($newFile);
            if (!is_dir($dataDirForFile)) {
                Tools::mkdir($dataDirForFile);
            }

            if ($upload) {
                $result = $info->move($fileSystemPath);
            } else {
                $result = copy($file, $fileSystemPath);
            }

            //When image, check EXIF for rotation if possible
            if($newFile->getType() == IFile::TYPE_IMAGE && in_array(strtolower($newFile->getExtension()), ['jpg', 'jpeg',' tiff']) && function_exists('exif_read_data')) {
                $exif = @exif_read_data($fileSystemPath);
                if ($exif && !empty($exif['Orientation'])) {
                    $img = NImage::fromFile($fileSystemPath);
                    switch ($exif['Orientation']) {
                        case 8:
                            //90
                            $img->rotate(90, 0);
                            break;
                        case 3:
                            //180
                            $img->rotate(180, 0);
                            break;
                        case 6:
                            //-90
                            $img->rotate(-90, 0);
                            break;
                    }

                    $img->save($fileSystemPath);
                }
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
        return $newFile;
    }

    /**
     * @param \SplFileInfo|FileUpload $info
     * @param null|IStructure $structure
     * @return mixed
     * @throws \Exception
     */
    public function processFile($info, IStructure $structure = null): ?IStructureFile
    {
        $newFile = $this->saveFile($info);

        // Upload
        if ($info instanceof FileUpload) {
            $name = pathinfo($info->getSanitizedName(), PATHINFO_FILENAME);
            $cnt = 0;

            do {
                $insertName = $name . ($cnt ? $cnt : '');
                $cnt++;
            } while (!$this->structureFileRepository->isNameFree($insertName, $structure));

            $structureFile = $this->structureFileRepository->createNewStructureFile($insertName, $newFile, $structure);
        } else {
            $name = $info->getBasename('.' . $info->getExtension());
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
     * @param string $filePath
     * @return string|null
     */
    public function getMimeType(string $filePath): ?string
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
     * @param \ZipArchive $zipArchive
     * @param array $structureTree
     * @param string $path
     * @throws \Exception
     */
    private function structureToZip(\ZipArchive $zipArchive, array $structureTree, string $path = ''): void
    {
        foreach ($structureTree AS $k => $v) {
            if (array_key_exists('directory', $v)) {
                $structure = $this->structureRepository->getOneById($v['directory']);
                $path .= '/' . $structure->getName();
            }


            if (array_key_exists('files', $v)) {
                foreach ($v['files'] AS $fileId) {
                    $structureFile = $this->structureFileRepository->getOneById($fileId);
                    $filePath = $this->getFileSystemPath($structureFile->getFile());

                    if (!is_file($filePath)) {
                        throw new \Exception(sprintf('File %s for pack not found', $filePath));
                    }
                    $zipArchive->addFile($filePath, $path . '/' . $structureFile->getName() . '.' . $structureFile->getFile()->getExtension());
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
     * @throws \Nette\Application\BadRequestException
     */
    public function downloadFile(IStructureFile $structureFile): FileResponse
    {
        $path = $this->getFileSystemPath($structureFile->getFile());
        return new FileResponse($path, $structureFile->getBasename(), $structureFile->getFile()->getMimeType());
    }

    /**
     * @param IStructure $structure
     * @return FileResponse
     * @throws \Exception
     */
    public function downloadStructure(IStructure $structure): FileResponse
    {
        $info = $this->getStructureFilesInfo($structure);


        $zipArchive = new \ZipArchive();
        $tmpfname = tempnam(sys_get_temp_dir(), 'structure-download');

        if (!$zipArchive->open($tmpfname, \ZipArchive::OVERWRITE)) {
            throw new \Exception('Failed to create ZIP archive');
        }

        $this->structureToZip($zipArchive, $info['tree']);

        $zipArchive->close();


        return new FileResponse($tmpfname, $structure->getName() . '.zip', 'application/zip');
    }

    /**
     * @param IFile $file
     */
    public function deleteFile(IFile $file): void
    {
        $this->fileRepository->deleteFile($file);
    }

    /**
     * @param IStructureFile $IStructureFile
     */
    public function deleteStructureFile(IStructureFile $IStructureFile): void
    {
        $this->structureFileRepository->deleteStructureFile($IStructureFile);
    }

    /**
     * @param IStructure $structure
     */
    public function deleteStructure(IStructure $structure): void
    {
        $this->structureRepository->deleteStructure($structure);
    }
    
    /**
     * @return string
     */
    public function getDataDir(): string
    {
        return $this->dataDir;
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function getDataDirForFile(IFile $file): string {
        return $this->getDataDir().'/'.substr($file->getSum(), 0, 2);
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function getFileSystemPath(IFile $file): string
    {
        return $this->getDataDirForFile($file).'/'.$file->getBasename();
    }

    /**
     * @param string $extenson
     * @return string
     */
    public function getIconFileSystemPath(string $extenson): string
    {
        $iconBaseName = $this->getIconBaseName($extenson);
        return $this->iconDir.'/'.$iconBaseName;
    }

    public function getIconBaseName(string $iconName): string {
        $iconName = $this->getIconNameForExtension($iconName);
        return $iconName.'.jpg';
    }

    /**
     * @param string $extension
     * @return string
     */
    public function getIconNameForExtension(string $extension): string
    {
        return (in_array($extension, $this->iconsSupported) ? $extension : 'txt');
    }
}


/**
 * Class FileNotFoundException
 * @package Salamek\Files
 */
class FileNotFoundException extends \RuntimeException
{

}
