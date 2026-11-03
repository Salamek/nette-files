<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Nette\Application\{Response, Responses\FileResponse};
use Nette\Http\{IRequest, IResponse};

final readonly class FileDeliveryResponse implements Response
{
    public function __construct(public string $path, public string $filename, public string $mime, public bool $download,
        public bool $public, private int $maxAge, private string $etag, private ?string $accel = null) {}

    public function send(IRequest $request, IResponse $response): void
    {
        $response->setHeader('Cache-Control', $this->public ? 'public, max-age=' . $this->maxAge . ', s-maxage=' . $this->maxAge : 'private, no-store');
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('Content-Security-Policy', "sandbox; default-src 'none'; style-src 'unsafe-inline'");
        $response->setContentType($this->mime);
        if ($this->public) {
            $response->setHeader('ETag', '"' . $this->etag . '"');
            if (in_array('"' . $this->etag . '"', array_map('trim', explode(',', (string) $request->getHeader('If-None-Match'))), true)) {
                $response->setCode(304); return;
            }
        }
        $response->setHeader('Content-Disposition', ($this->download ? 'attachment' : 'inline') . '; filename="' . $this->filename . '"; filename*=UTF-8\'\'' . rawurlencode($this->filename));
        if ($this->accel !== null) { $response->setHeader('X-Accel-Redirect', $this->accel); return; }
        if ($request->isMethod('HEAD')) { $response->setHeader('Content-Length', (string) filesize($this->path)); return; }
        (new FileResponse($this->path, $this->filename, $this->mime, $this->download))->send($request, $response);
    }
}
