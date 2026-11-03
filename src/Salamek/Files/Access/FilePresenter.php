<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Nette\Application\{BadRequestException, IPresenter, Request, Response};
use Nette\Http\IResponse;

final class FilePresenter implements IPresenter
{
    public function __construct(private FileDelivery $delivery, private IResponse $response) {}

    public function run(Request $request): Response
    {
        // Failures, including unknown references/hashes, must never enter a shared cache.
        $this->response->setHeader('Cache-Control', 'private, no-store');
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            $this->response->setHeader('Allow', 'GET, HEAD'); throw new BadRequestException('Method not allowed.', 405);
        }
        $parameters = [];
        foreach (['provider', 'reference', 'sum', 'variant'] as $key) {
            $value = $request->getParameter($key);
            if (!is_string($value)) { throw new BadRequestException(); }
            $parameters[] = $value;
        }
        return $this->delivery->createResponse(...$parameters);
    }
}
