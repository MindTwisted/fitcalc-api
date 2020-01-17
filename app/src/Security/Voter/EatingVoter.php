<?php

namespace App\Security\Voter;

use App\Entity\Eating;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EatingVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Eating;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $eating = $subject;

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $this->canEdit($eating, $user);
        }

        return false;
    }

    /**
     * @param Eating $eating
     * @param User $user
     *
     * @return bool
     */
    private function canEdit(Eating $eating, User $user): bool
    {
        return $eating->getUser() && $eating->getUser()->getId() === $user->getId();
    }
}
