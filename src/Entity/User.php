<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields = {"email"},
 *      message="Un compte est déjà existant à cette adresse email !"
 * )
 * @UniqueEntity(
 *      fields = {"username"},
 *      message="Ce nom d'utilisateur est déjà existant, veuillez en saisir un nouveau !"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner un Email")
     * @Assert\Email(message="Veuillez saisir un Email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez saisir un nom d'utilisateur")
     * @Assert\Length(
     *      min="2",
     *      minMessage="Votre nom d'utilisateur doit contenir minimum 2 caractères"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min="8",
     *      minMessage="Votre mot de passe doit faire minimum 8 caractères"
     * )
     * @Assert\EqualTo(
     *      propertyPath="confirm_password",
     *      message="Les mots de passe ne correspondent pas"
     * )
     */
    private $password;

    /**
     * @Assert\EqualTo(
     *      propertyPath="password",
     *      message="Les mots de passe ne correspondent pas"
     * )
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    // cette méthode est uniquement destinée à nettoyer les mots de passe en texte brut éventuellement stockés
    public function eraseCredentials()
    {
    }

    // Renvoit la chaîne de caractères non encodée que l'utilisateur a saisie, qui est utilisée à l'origine pour encoder le mot de passe
    public function getSalt()
    {
    }

    // Cette fonction renvoit un tableau de chaîne de caractères.
    // Renvoit les rôles accordés à l'utilisateur
    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
