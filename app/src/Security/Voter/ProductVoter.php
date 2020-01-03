<?php

namespace App\Security\Voter;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{
    const EDIT = 'edit';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT])
            && $subject instanceof Product;
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

        $product = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($product, $user);
        }

        return false;
    }

    /**
     * @param Product $product
     * @param User $user
     *
     * @return bool
     */
    private function canEdit(Product $product, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $product->getUser() && $product->getUser()->getId() === $user->getId();
    }
}
