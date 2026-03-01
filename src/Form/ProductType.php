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

/**
 * Formulari de creació i edició de productes.
 *
 * Exposa únicament els camps que l'usuari pot introduir:
 * name, description, price i image.
 *
 * Els camps owner, createdAt i updatedAt NO apareixen aquí —
 * s'assignen programàticament al controlador per seguretat.
 * Exposar l'owner al formulari permetria manipular-lo des del HTML.
 *
 * Les validacions es defineixen aquí amb constraints en lloc d'anotacions
 * a l'entitat, per mantenir la lògica de validació del formulari
 * separada de la definició de l'entitat.
 */
class ProductType extends AbstractType
{
    /**
     * Construeix el formulari afegint els camps i les seves validacions.
     *
     * @param FormBuilderInterface $builder Constructor del formulari
     * @param array                $options Opcions del formulari (no usades aquí)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Títol ─────────────────────────────────────────────────────────
            // Camp obligatori. Entre 3 i 255 caràcters.
            ->add('name', TextType::class, [
                'label' => 'Títol',
                'constraints' => [
                    new NotBlank(message: 'El títol no pot estar buit.'),
                    new Length(
                        min: 3,
                        minMessage: 'El títol ha de tenir mínim {{ limit }} caràcters.',
                        max: 255,
                        maxMessage: 'El títol no pot superar {{ limit }} caràcters.'
                    ),
                ],
            ])

            // ── Descripció ────────────────────────────────────────────────────
            // Camp obligatori. Mínim 10 caràcters per garantir contingut útil.
            ->add('description', TextareaType::class, [
                'label' => 'Descripció',
                'constraints' => [
                    new NotBlank(message: 'La descripció no pot estar buida.'),
                    new Length(
                        min: 10,
                        minMessage: 'La descripció ha de tenir mínim {{ limit }} caràcters.'
                    ),
                ],
            ])

            // ── Preu ──────────────────────────────────────────────────────────
            // Camp obligatori. Ha de ser un número positiu amb 2 decimals.
            // NumberType amb scale: 2 formata el valor amb dos decimals.
            ->add('price', NumberType::class, [
                'label' => 'Preu (€)',
                'scale' => 2,
                'constraints' => [
                    new NotBlank(message: 'El preu no pot estar buit.'),
                    new Positive(message: 'El preu ha de ser un valor positiu.'),
                ],
            ])

            // ── Imatge ────────────────────────────────────────────────────────
            // Camp opcional. Si s'especifica, ha de ser una URL vàlida amb TLD.
            // Si es deixa buit, el controlador genera una imatge automàtica amb Picsum.
            ->add('image', UrlType::class, [
                'label'    => 'URL de la imatge (opcional)',
                'required' => false,
                'constraints' => [
                    // requireTld: true valida que la URL tingui domini real (ex: .com, .net)
                    new Url(
                        message: 'La imatge ha de ser una URL vàlida.',
                        requireTld: true
                    ),
                ],
            ])
        ;
    }

    /**
     * Configura les opcions del formulari.
     * Vincula el formulari a l'entitat Product perquè Symfony
     * mapegi automàticament els camps als getters/setters de l'entitat.
     *
     * @param OptionsResolver $resolver Resolutor d'opcions de Symfony Forms
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}