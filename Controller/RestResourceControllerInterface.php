<?php
namespace Dontdrinkandroot\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RestResourceControllerInterface
 *
 * @package Dontdrinkandroot\RestBundle\Controller
 */
interface RestResourceControllerInterface
{
    public function listAction(Request $request);

    public function postAction(Request $request);

    public function getAction(Request $request, $id);

    public function putAction(Request $request, $id);

    public function deleteAction(Request $request, $id);

    public function listSubresourceAction(Request $request, $id, string $subresource);

    public function postSubresourceAction(Request $request, $id, string $subresource);

    public function putSubresourceAction(Request $request, $id, string $subresource, $subId);

    public function deleteSubresourceAction(Request $request, $id, string $subresource, $subId);
}
