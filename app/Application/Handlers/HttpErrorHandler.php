<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Views\Twig;
use Throwable;

class HttpErrorHandler extends ErrorHandler
{
    /**
     * @var Twig
     */
    private $twig;

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        Twig $twig)
    {
        parent::__construct($callableResolver, $responseFactory);
        $this->twig = $twig;
    }

    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = $this->exception->getCode();

        $response = $this->responseFactory->createResponse($statusCode);
        $detail = $this->displayErrorDetails
            ? "{$exception->getFile()} @ {$exception->getLine()}
            {$exception->getTraceAsString()}"
            : null;
        $title = $exception instanceof HttpException
            ? $exception->getTitle()
            : get_class($exception);
        try {
            return $this->twig->render($response, 'error.twig', [
                'title' => $title,
                'detail' => $detail,
            ]);
        } catch (Throwable $e) {
            $response->getBody()->write('<h1>error</h1>');
            return $response
                ->withStatus(500);
        }
    }
}
