<?php

namespace App\Infrastructure\Controller;

use App\Domain\Command\SendCmd;
use App\Domain\Dto\SendDto;
use App\Infrastructure\Dto\ApiProblemResponseDto;
use App\Infrastructure\Dto\SuccessResponseDto;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class InboxController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/inbox/send', name: 'inbox-send', methods: 'POST', format: 'json')]
    #[OA\Response(response: 200, description: 'Returns identifier of created address.', content: new Model(type: SuccessResponseDto::class))]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    public function send(
        #[MapRequestPayload] SendDto $dto,
        SendCmd $cmd
    ): JsonResponse {
        $id = $cmd->send($dto);

        return new JsonResponse(new SuccessResponseDto(['id' => $id]), Response::HTTP_CREATED);
    }
}
