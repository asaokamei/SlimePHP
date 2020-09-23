<?php
namespace App\Controllers\Forms;

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function onGet(): ResponseInterface
    {
        return $this->view('form.twig', [
            'form' => $this->makeForm()->createView(),
            'csrf' => [$this->csrfTokenName(), $this->csrfTokenValue()],
        ]);
    }

    /**
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function onPost(): ResponseInterface
    {
        $form = $this->makeForm();
        $inputs = $this->request->getParsedBody();
        $form->submit($inputs);
        $data = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        }
        return $this->view('form.twig', [
            'form' => $form->createView(),
            'csrf' => [$this->csrfTokenName(), $this->csrfTokenValue()],
            'data' => $data,
        ]);
    }

    private function makeForm(): FormInterface
    {
        return $this->form()->createNamedBuilder('test')
            ->add('name', TextType::class)
            ->add('comments', TextareaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
    }
}