<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Títol del producte: obligatori, entre 3 i 255 caràcters
            ->add('name', TextType::class, [
                'label' => 'Títol',
                'constraints' => [
                    new NotBlank(message: 'El títol no pot estar buit.'),
                    new Length(
                        min: 3, minMessage: 'El títol ha de tenir mínim {{ limit }} caràcters.',
                        max: 255, maxMessage: 'El títol no pot superar {{ limit }} caràcters.'
                    ),
                ],
            ])
            // Descripció: obligatòria, mínim 10 caràcters
            ->add('description', TextareaType::class, [
                'label' => 'Descripció',
                'constraints' => [
                    new NotBlank(message: 'La descripció no pot estar buida.'),
                    new Length(min: 10, minMessage: 'La descripció ha de tenir mínim {{ limit }} caràcters.'),
                ],
            ])
            // Preu: obligatori, ha de ser positiu
            ->add('price', NumberType::class, [
                'label' => 'Preu (€)',
                'scale' => 2,
                'constraints' => [
                    new NotBlank(message: 'El preu no pot estar buit.'),
                    new Positive(message: 'El preu ha de ser un valor positiu.'),
                ],
            ])
            // Imatge: opcional, ha de ser una URL vàlida si s'especifica
            ->add('image', UrlType::class, [
                'label' => 'URL de la imatge (opcional)',
                'required' => false,
                'constraints' => [
                    new Url(message: 'La imatge ha de ser una URL vàlida.', requireTld: true),
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}