<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Nette\Http\IRequest;

final class FileUrl
{
    public function __construct(private IRequest $request, private VariantRegistry $variants) {}

    public function create(FileReference $reference, string $variant = 'original', bool $absolute = false): string
    {
        if (!$this->variants->has($variant)) { throw new \InvalidArgumentException('Unknown file variant.'); }
        $url = $this->request->getUrl();
        $base = $absolute ? rtrim($url->getBaseUrl(), '/') . '/' : $url->getBasePath();
        return $base . 'files/' . $reference->provider . '/' . rawurlencode($reference->reference) . '/' . $reference->file->getSum() . '/' . $variant;
    }
}
