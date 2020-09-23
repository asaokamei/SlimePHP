<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Views\Twig;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractController
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param LoggerInterface $logger
     * @param Twig $view
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(LoggerInterface $logger, Twig $view, FormFactoryInterface $formFactory)
    {
        $this->logger = $logger;
        $this->view = $view;
        $this->formFactory = $formFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws HttpMethodNotAllowedException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        $method = 'on' . ($request->getAttribute('_method') ?? $request->getMethod());
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        throw new HttpMethodNotAllowedException($request);
    }

    /**
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function view(string $template, array $data = []): ResponseInterface
    {
        return $this->view->render($this->response, $template, $data);
    }

    protected function form(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    protected function csrfTokenName(): string
    {
        return $this->request->getAttribute('_csrf_name');
    }

    protected function csrfTokenValue(): string
    {
        return $this->request->getAttribute('_csrf_value');
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }
}
