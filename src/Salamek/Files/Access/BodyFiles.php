<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Nette\Http\IRequest;
use Salamek\Files\Models\{IFile, IFileRepository};

/** Recognizes local file embeds in stored HTML and binds their URLs to the owning content. */
final class BodyFiles
{
    private array $files = [];
    public function __construct(private IFileRepository $repository, private FileUrl $urls, private IRequest $request) {}

    public function rewrite(string $html, string $provider, int|string $owner): string
    {
        $embeddedUrls = array_fill_keys($this->urlsFromHtml($html), true);
        return preg_replace_callback('~\b(src|href|poster)(\s*=\s*)(["\'])(.*?)\3~is', function (array $match) use ($provider, $owner, $embeddedUrls): string {
            $url = html_entity_decode($match[4], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (!isset($embeddedUrls[$url])) { return $match[0]; }
            $sum = $this->sumFromUrl($url);
            $file = $sum ? $this->find($sum) : null;
            if (!$file) { return $match[0]; }
            $url = $this->urls->create(new FileReference($provider, $owner . '-' . $sum, $file));
            return $match[1] . $match[2] . $match[3] . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $match[3];
        }, $html);
    }

    public function resolve(string $html, string $sum): ?IFile
    {
        foreach ($this->urlsFromHtml($html) as $url) {
            if ($this->sumFromUrl($url) === $sum) { return $this->find($sum); }
        }
        return null;
    }

    /** Only actual rendered link/embed attributes establish an attachment relationship. */
    private function urlsFromHtml(string $html): array
    {
        if ($html === '') { return []; }
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $attributes = (new \DOMXPath($document))->query('//img/@src | //a/@href | //video/@src | //video/@poster | //audio/@src | //source/@src | //iframe/@src');
        $urls = [];
        foreach ($attributes as $attribute) { $urls[] = $attribute->value; }
        return $urls;
    }

    private function find(string $sum): ?IFile
    {
        if (!array_key_exists($sum, $this->files)) { $this->files[$sum] = $this->repository->getOneBySum($sum); }
        return $this->files[$sum];
    }

    private function sumFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || (isset($parts['scheme']) && !in_array(strtolower($parts['scheme']), ['http', 'https'], true))) { return null; }
        if (isset($parts['host']) && strcasecmp($parts['host'], $this->request->getUrl()->getHost()) !== 0) { return null; }
        $path = $parts['path'] ?? '';
        $base = $this->request->getUrl()->getBasePath();
        if (!str_starts_with($path, $base)) { return null; }
        $path = substr($path, strlen($base));
        if (preg_match('~^(?:data/(?:[a-f0-9]{2}/)?|webtemp/[^/]+/)((?:[a-f0-9]{32}|[a-f0-9]{64}))\.[a-zA-Z0-9]+$~D', $path, $m)) { return $m[1]; }
        if (preg_match('~^files/[a-z][a-z0-9.-]*/[a-zA-Z0-9_-]+/((?:[a-f0-9]{32}|[a-f0-9]{64}))/(?:original|download)$~D', $path, $m)) { return $m[1]; }
        return null;
    }
}
