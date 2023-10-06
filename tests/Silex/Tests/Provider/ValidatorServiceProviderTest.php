<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\Custom;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * ValidatorServiceProvider.
 *
 * Javier Lopez <f12loalf@gmail.com>
 */
class ValidatorServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application();
        $app->register(new ValidatorServiceProvider());

        return $app;
    }

    public function testRegisterWithCustomValidators()
    {
        $app = new Application();

        $app['custom.validator'] = function () {
            return new CustomValidator();
        };

        $app->register(new ValidatorServiceProvider(), [
            'validator.validator_service_ids' => [
                'test.custom.validator' => 'custom.validator',
            ],
        ]);

        return $app;
    }

    /**
     * @depends testRegisterWithCustomValidators
     */
    public function testConstraintValidatorFactory($app)
    {
        $this->assertInstanceOf('Silex\Provider\Validator\ConstraintValidatorFactory', $app['validator.validator_factory']);

        $validator = $app['validator.validator_factory']->getInstance(new Custom());
        $this->assertInstanceOf('Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\CustomValidator', $validator);
    }

    /**
     * @depends testRegister
     */
    public function testConstraintValidatorFactoryWithExpression($app)
    {
        $constraint = new Assert\Expression('true');
        $validator = $app['validator.validator_factory']->getInstance($constraint);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\ExpressionValidator', $validator);
    }

    /**
     * @depends testRegister
     */
    public function testValidatorServiceIsAValidator($app)
    {
        $this->assertTrue($app['validator'] instanceof ValidatorInterface);
    }

    /**
     * @depends      testRegister
     * @dataProvider getTestValidatorConstraintProvider
     */
    public function testValidatorConstraint($email, $isValid, $nbGlobalError, $nbEmailError, $app)
    {
        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
        ]);

        $builder = $app['form.factory']->createBuilder('Symfony\Component\Form\Extension\Core\Type\FormType', [], [
            'constraints' => $constraints,
        ]);

        $form = $builder
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', ['label' => 'Email'])
            ->getForm();

        $form->submit(['email' => $email]);

        $this->assertEquals($isValid, $form->isValid());
        $this->assertCount($nbGlobalError, $form->getErrors());
        $this->assertCount($nbEmailError, $form->offsetGet('email')->getErrors());
    }

    public function getTestValidatorConstraintProvider()
    {
        // Email, form is valid, nb global error, nb email error
        return [
            ['', false, 0, 1],
            ['not an email', false, 0, 1],
            ['email@sample.com', true, 0, 0],
        ];
    }

    public function getAddResourceData()
    {
        return [[false], [true]];
    }
}
