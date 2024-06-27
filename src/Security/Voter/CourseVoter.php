<?php

namespace App\Security\Voter;

use App\Entity\Course;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourseVoter extends Voter{

    private const VIEW = 'view';
    private const DELETE = 'delete';
    private const EDIT = 'edit';
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::DELETE, self::EDIT]) && $subject instanceof Course;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
                case self::DELETE:
                    return $this->canDelete($subject, $user);
                case self::EDIT:
                    return $this->canEdit($subject, $user);
        }

        return false;
    }

    private function canView(Course $course, User $user): bool
    {
        return $this->security->isGranted('ROLE_USER') &&
            ($this->isCourseFollowedByUser($user, $course) ||
                $this->security->isGranted('ROLE_ADMIN') ||
                $this->isCourseCreatedByUser($user, $course));
    }

    private function canDelete(Course $course, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN') ||
            $this->isCourseCreatedByUser($user, $course);
    }


    private function canEdit(Course $course, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN') ||
            $this->isCourseCreatedByUser($user, $course);
    }

    private function isCourseFollowedByUser(User $user, Course $course): bool
    {
        return $user->getCoursesFollowed()->contains($course);
    }

    private function isCourseCreatedByUser(User $user, Course $course): bool
    {
        return $course->getInstructor() == $user;
    }
}
