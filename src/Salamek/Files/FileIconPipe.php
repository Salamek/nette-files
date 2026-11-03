<?php declare(strict_types=1);
namespace Salamek\Files;
use Nette\Http\IRequest;

/** Generic package icons are public assets, never uploaded file previews. */
class FileIconPipe
{
    public function __construct(private FileStorage $storage, private IRequest $request, private string $iconPath = 'assets/file/ico') {}

    public function request(?string $iconName = null, ?string $size = null): string
    {
        $iconName ??= 'txt';
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $iconName)) { throw new \InvalidArgumentException('Invalid icon name.'); }
        return $this->request->getUrl()->getBasePath() . trim($this->iconPath, '/') . '/' . $this->storage->getIconBaseName($iconName);
    }
}
