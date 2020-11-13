<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Workflow;


class WorkflowController extends Controller
{
    use Workflow;
    public function InsertChildsIntoGrid(Request $request)
    {
       

    }

    public function AsociateMultipleDocs(Request $request)
    {
        $documents = explode(",", $request->documents);
        $response = $this->newAssocDocumentMultiple($documents,$request->workflowid,$request->activityid);
        return $response;
    }
}