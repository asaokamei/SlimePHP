<?php
namespace App\Actions\Forms;

use App\Actions\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    protected function action(): ResponseInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->view('form.twig', [
            'form' => $this->form()->createNamedBuilder('test')
            ->add('name', TextType::class)
            ->add('comments', TextareaType::class)
            ->getForm()
            ->createView(),
        ]);
    }
}