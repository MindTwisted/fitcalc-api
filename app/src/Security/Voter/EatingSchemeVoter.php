<?php

namespace App\Security\Voter;


use App\Entity\EatingScheme;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EatingSchemeVoter extends Voter
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
            && $subject instanceof EatingScheme;
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

        $eatingScheme = $subject;

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $this->canEdit($eatingScheme, $user);
        }

        return false;
    }

    /**
     * @param EatingScheme $eatingScheme
     * @param User $user
     *
     * @return bool
     */
    private function canEdit(EatingScheme $eatingScheme, User $user): bool
    {
        return $eatingScheme->getUser() && $eatingScheme->getUser()->getId() === $user->getId();
    }
}
