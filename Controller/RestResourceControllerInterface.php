<?php
namespace Dontdrinkandroot\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface RestResourceControllerInterface
{
    public function listAction(Request $request);

    public function postAction(Request $request);

    public function getAction(Request $request, $id);

    public function putAction(Request $request, $id);

    public function deleteAction(Request $request, $id);

    public function listSubresourceAction(Request $request, $id);

    public function postSubresourceAction(Request $request, $id);

    public function putSubresourceAction(Request $request, $id, $subId);

    public function deleteSubresourceAction(Request $request, $id, $subId);
}
