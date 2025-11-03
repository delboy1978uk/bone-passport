<?php

namespace Bone\Passport\Middleware;

use Bone\Exception;
use Del\Entity\User;
use Del\Passport\Entity\PassportRole;
use Del\Passport\PassportControl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PassportControlMiddleware implements MiddlewareInterface
{
    private string $role;
    private ?string $entityAttribute = null;

    public function __construct(
        private PassportControl $passportControl
    ) {}

    public function withOptions(string $role, string $entityAttribute = null): PassportControlMiddleware
    {
        $this->role = $role;
        $this->entityAttribute = $entityAttribute;

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $role = $this->role;
        $entityAttribute = $this->entityAttribute;
        $entityId = null;

        if ($entityAttribute) {
            $entityId = (int) $request->getAttribute($entityAttribute);
        }

        $passport = $this->passportControl->findUserPassport($user->getId());
        $entitlements = $passport->getEntitlements();
        $authorised = false;

        /** @var PassportRole $entitlement */
        foreach ($entitlements as $entitlement) {

            if ($entitlement->getRole()->getRoleName() === $role) {
                if (!$entityId) {
                    $authorised = true;
                    break;
                }

                if ($entityId && $entitlement->getEntityId() === $entityId) {
                    $authorised = true;
                }
            } else {
                foreach ($entitlement->getRole()->getChildren() as $child) {
                    if ($child->getRoleName() === $role) {
                        if (!$entityId) {
                            $authorised = true;
                            break;
                        }

                        if ($entityId && $entitlement->getEntityId() === $entityId) {
                            $authorised = true;
                        }
                    }
                }
            }
        }

        if (!$authorised) {
            throw new Exception('Unauthorised', 403);
        }

        return $handler->handle($request);
    }
}
