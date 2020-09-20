<?php

namespace App\Resolver;

use App\Model\Customer;
use App\Service\JwtManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerResolver implements ArgumentValueResolverInterface
{
    protected JwtManager $jwtManager;

    public function __construct(JwtManager $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Customer::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $token = (string) $request->headers->get('authorization', '');

        $customer = $this->jwtManager->getCustomer($token);

        if (empty($customer)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'unauthorized');
        }

        yield $customer;
    }
}
