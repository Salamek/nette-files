# Nette Files

Private file storage, contextual file URLs and authorized delivery for Nette.
Original upload bytes are immutable; EXIF orientation is applied to generated images.
Private directories must be readable by the application and, when accelerated, the
web-server user/group. New storage directories respect umask and are not world-writable.
Version 3 separates URL generation from image transformation. Uploaded originals
and generated variants belong outside the document root.

## Access providers

Implement `Salamek\Files\Access\IFileAccessProvider` and register the implementation
as a DI service. Each provider has a globally unique static `getId()` such as
`gallery.picture`. A package may register multiple providers. Duplicate identifiers
are rejected during container compilation.

`resolve(string $reference): ?FileAccess` must find the actual attachment, verify
its owning content and authorize the current visitor. Return null for missing or
denied references. Return `new FileAccess($file, public: true)` only if the file is
available to everyone. For authenticated access, return a private result (the
default). Optional `filename` controls the download name, and `variants` restricts
which configured representations this attachment permits.

Reference strings are opaque to shared delivery and consist of 1–180 ASCII letters,
digits, underscores or hyphens. Composite references can use a base64url encoding.
Neither a reference nor a content checksum grants access by itself.

## URLs and images

```php
$reference = FileReference::attachment('gallery.picture', $picture->getId(), $picture->getStructureFile());
$url = $fileUrl->create($reference, 'thumbnail');
// /files/gallery.picture/42/<checksum>/thumbnail
```

Import these classes from `Salamek\Files\Access`. The application routes that URL
to `FilePresenter` (Dravencms File supplies the router and presenter mapping).
The shared endpoint checks provider/reference, current attachment checksum and
allowed variant before reading either the original or a generated image. Existing
MD5 records remain readable; new uploads use SHA-256. Checksums identify content,
not permissions. URLs do not expire and contain no access signatures.

Register fixed image recipes in the extension:

```neon
files:
    dataDir: %appDir%/../var/files/data
    webTempDir: %appDir%/../var/files/cache
    iconDir: %wwwDir%/assets/file/ico
    iconPath: assets/file/ico
    publicMaxAge: 3600
    variants:
        thumbnail: [304, 247, exact]
```

`ImagePipe::request($reference, '304x247', 'exact')` finds that registered recipe and
returns its URL. It performs no disk reads or transformations while rendering a
page. Raw `IFile`/`IStructureFile` arguments are no longer accepted: callers must
supply attachment context. Null produces a generic placeholder with no user data.
The `{img ...}` and `n:img` Latte helpers accept the same contextual reference.
`{file $reference, 'download'}` generates a download URL. Generic file icons are
public package assets and contain no uploaded content.

Only registered dimensions/flags can be requested. Image output is generated under
a lock and published through an atomic rename. All generated images remain private
on disk; a warm image never bypasses authorization. SVG responses are sandboxed;
unsupported active document types are forced to download. Original PDFs can display
inline; `download` forces attachment disposition for any type.

## Caching and web-server delivery

Public results have `Cache-Control: public, max-age=<TTL>, s-maxage=<TTL>` and ETags.
A properly configured reverse proxy can serve subsequent requests without PHP.
Private results and endpoint errors use `private, no-store`. Never override these
headers or cache private responses. Admin previews are private. Unpublishing stops
new authorized public responses; existing public cache entries expire after TTL.

GET and HEAD are supported. The PHP response supports range downloads. To delegate
transfer to Nginx, optionally map private filesystem roots to internal locations:

```neon
files:
    accelMapping:
        %appDir%/../var/files/data: /_files_data
        %appDir%/../var/files/cache: /_files_cache
```

```nginx
location /_files_data/ {
    internal;
    alias /srv/project/var/files/data/;
}
location /_files_cache/ {
    internal;
    alias /srv/project/var/files/cache/;
}
```

Do not add static `try_files` shortcuts for `/files/` into the private directories.
Use a response cache that respects public/private headers. If combining an upstream
cache and accelerated delivery, verify the final response headers and cache behavior
on the actual server; `X-Accel-Redirect` alone delegates transfer, not authorization
or response caching. The default PHP delivery requires no Nginx-specific configuration.

## Stored rich text

`BodyFiles::rewrite($html, 'article.body', $translationId)` converts recognized local
legacy `/data`/image-preview URLs and editor file URLs to contextual references.
`BodyFiles::resolve($storedHtml, $sum)` returns a file only if a recognized local
embed/link actually references it. The owning content provider must separately
check publication/current-user access. Plain checksum text and external URLs do not
grant access. The `fileContent` Latte filter calls this rewriter. Stored database
content is left intact; returned HTML uses the new URLs.

## Upgrading storage

Install the matching Dravencms File release, update `files` configuration, migrate
the database checksum column and move old public data before resuming requests or
uploads. Existing file/attachment IDs and checksums need no rewrite. Keep private
data persistent across application release directories. See Dravencms File's
`file:hash:migrate` and `file:storage:migrate` commands for an explicit preview/apply
workflow. Webloader CSS/JS are public application assets and must remain available.
