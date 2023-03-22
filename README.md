# Nette Files

This is a file storage for [Nette Framework](http://nette.org/)

Nette Files implements virtual directory structure (`IStructure`, `IStructureRepositry`) all files are stored in `dataDir` as their md5sum.ext, file info is implemented in `IStructureFile`, `IStructureFileRepository` and position of file in structure is implemented in `IFile` and `IFileRepository`


## Instalation

The best way to install salamek/nette-files is using  [Composer](http://getcomposer.org/):


```sh
$ composer require salamek/nette-files
```

Then you have to register extension in `config.neon`.

```yaml
    extensions:
        files: Salamek\Files\DI\FilesExtension

    files:
        dataDir: %wwwDir%/data
        webTempDir: %wwwDir%/webtemp
        webTempPath: 'webtemp'
        iconDir: %wwwDir%/assets/file
```

You will need to implement:
* IFile entity representing single file
* IFileRepository repository for IFile
* IStructure entity representing single structure item (Folder/Directory)
* IStructureRepository repository for IStructure
* IStructureFile entity representing connection of IFile to IStructure (in what folder is what file)
* IStructureFileRepository repository for IStructureFile

Package contains trait, which you will have to use in class, where you want to use file storage. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter {

    use Salamek\Files\TImagePipe;

}

```

## Usage

### Saving files

```php
/** @var Salamek\Files\FileStorage $fileStorage */
/** @var \SplFileInfo|FileUpload $fileUpload */
$fileStorage->processFile($fileUpload); // Saves file to `dataDir` as %wwwDir%/data/md5sum_of_file.ext

```

### Using ImagePipe in Latte

```html
<a href="{img IFile|IStructureFile}"><img n:img="IFile|IStructureFile, 200x200, fill"></a>
```

output:

```html
<a href="%wwwDir%/data/md5sum_of_file.ext"><img src="%wwwDir%/data/200x200_fill_md5sum_of_file.ext"></a>
```

### Resizing ImagePipe flags

For resizing (third argument) you can use these keywords - `fit`, `fill`, `exact`, `stretch`, `shrink_only`, `stretch`, `fit_exact`, `crop`. For details see comments above [these constants](http://api.nette.org/2.0/source-common.Image.php.html#105)
