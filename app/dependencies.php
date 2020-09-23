<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        /**
         * logger/Monolog
         */
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        /**
         * view/Twig
         */
        Twig::class => function (ContainerInterface $c) {
            // here's settings.
            $settings = $c->get('settings');

            // the path to TwigBridge library so Twig can locate the
            // form_div_layout.html.twig file
            $appVariableReflection = new ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
            $vendorTwigBridgeDirectory = dirname($appVariableReflection->getFileName());
            $paths = [
                __DIR__ . '/templates',
                $vendorTwigBridgeDirectory . '/Resources/views/Form',
            ];
            $twig = Twig::create($paths, [
                'cache' => $settings['cache-path'] . '/twig',
                'auto_reload' => true,
            ]);

            // the Twig file that holds all the default markup for rendering forms
            // this file comes with TwigBridge
            $defaultFormTheme = 'form_div_layout.html.twig';
            $formEngine = new TwigRendererEngine([$defaultFormTheme], $twig->getEnvironment());
            $twig->addRuntimeLoader(new FactoryRuntimeLoader([
                FormRenderer::class => function () use ($formEngine) {
                    return new FormRenderer($formEngine);
                },
            ]));

            // adds the FormExtension to Twig
            $twig->addExtension(new FormExtension());
            $twig->addExtension(new TranslationExtension($c->get('translator')));
            return $twig;
        },
        'view' => DI\get(Twig::class),

        /**
         * forms/Symfony Forms
         */
        FormFactoryInterface::class => function () {

            // creates the validator - details will vary
            $validator = Validation::createValidator();

            return Forms::createFormFactoryBuilder()
                ->addExtension(new ValidatorExtension($validator))
                ->getFormFactory();
        },
        'forms' => DI\get(FormFactoryInterface::class),

        /**
         * translator/Symfony's Translator
         */
        'translator' => function (ContainerInterface $c) {

            $settings = $c->get('settings');
            $projectRoot = $settings['projectRoot'];
            $vendorDirectory = $projectRoot . '/vendor/';
            $vendorFormDirectory = $vendorDirectory . '/symfony/form';
            $vendorValidatorDirectory = $vendorDirectory . '/symfony/validator';

            $translator = new Translator('en');
            // somehow load some translations into it
            $translator->addLoader('xlf', new XliffFileLoader());
            $translator->addResource(
                'xlf',
                $projectRoot . '/app/translations/messages.en.xlf',
                'en'
            );
            $translator->addResource(
                'xlf',
                $vendorFormDirectory . '/Resources/translations/validators.en.xlf',
                'en',
                'validators'
            );
            $translator->addResource(
                'xlf',
                $vendorValidatorDirectory . '/Resources/translations/validators.en.xlf',
                'en',
                'validators'
            );

            return $translator;
        },
    ]);

};
