<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Formulari de registre de nous usuaris.
 *
 * Gestiona els camps necessaris per crear un compte:
 * name, email, plainPassword i agreeTerms.
 *
 * Nota important sobre plainPassword:
 * - El camp té 'mapped' => false, és a dir, NO es mapeja directament
 *   a cap propietat de l'entitat User.
 * - El controlador l'obté manualment amb $form->get('plainPassword')->getData()
 *   i el hasheja abans de desar-lo. Això és necessari perquè User::$password
 *   emmagatzema el hash, no la contrasenya en text pla.
 *
 * Nota sobre agreeTerms:
 * - També té 'mapped' => false perquè no existeix cap camp agreeTerms
 *   a l'entitat User. Només s'usa per validar l'acceptació de termes
 *   durant el registre.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Construeix el formulari de registre amb tots els camps i validacions.
     *
     * @param FormBuilderInterface $builder Constructor del formulari
     * @param array                $options Opcions del formulari (no usades aquí)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Nom ───────────────────────────────────────────────────────────
            // Camp obligatori. Entre 2 i 50 caràcters.
            // Es mostrarà a la navbar i a les targetes de producte.
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(message: 'Introdueix el teu nom.'),
                    new Length(min: 2, max: 50),
                ],
            ])

            // ── Email ─────────────────────────────────────────────────────────
            // Camp obligatori. Actua com a identificador únic per al login.
            // La unicitat es valida a nivell d'entitat amb #[UniqueEntity].
            ->add('email', EmailType::class, [
                'label' => 'Correu electrònic',
                'constraints' => [
                    new NotBlank(message: 'Introdueix el teu email.'),
                    new Email(message: 'El format del correu electrònic no és vàlid.'),
                ],
            ])

            // ── Acceptació de termes ──────────────────────────────────────────
            // mapped: false — no existeix a l'entitat User, només valida el checkbox.
            // IsTrue garanteix que l'usuari hagi marcat el checkbox per registrar-se.
            ->add('agreeTerms', CheckboxType::class, [
                'label'  => 'Accepto els termes i condicions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: 'Has d\'acceptar els termes per registrar-te.'),
                ],
            ])

            // ── Contrasenya ───────────────────────────────────────────────────
            // mapped: false — no es mapeja a User::$password directament.
            // El controlador l'obté, el hasheja i el desa com a hash.
            // Mínim 6 caràcters | Màxim 4096 (límit de seguretat de Symfony).
            // autocomplete: 'new-password' indica al navegador que no autoompli.
            ->add('plainPassword', PasswordType::class, [
                'label'  => 'Contrasenya',
                'mapped' => false,
                'attr'   => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Introdueix una contrasenya.'),
                    new Length(
                        min: 8,
                        minMessage: 'La contrasenya ha de tenir mínim {{ limit }} caràcters.',
                        // 4096 és el límit màxim de Symfony per prevenir atacs de DoS
                        // mitjançant contrasenyes extremadament llargues
                        max: 4096,
                    ),
                ],
            ])
        ;
    }

    /**
     * Configura les opcions del formulari.
     * Vincula el formulari a l'entitat User perquè Symfony
     * mapegi automàticament name i email als setters corresponents.
     * Els camps amb 'mapped' => false s'exclouen d'aquest mapatge.
     *
     * @param OptionsResolver $resolver Resolutor d'opcions de Symfony Forms
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}