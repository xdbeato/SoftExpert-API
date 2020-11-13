<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\Document;
use App\Traits\Workflow;
use Illuminate\Support\Facades\Log;

class GenerateMultipleSoftexpertDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Document, Workflow;
    private $rows;
    private $category;
    private $documentTitle;
    private $activityId;
    private $workflowId;
    private $automaticExecution;

    /**
     * Create a new job instance.
     *
     * @param array $documents
     * @param string $category
     * @param string $documentTitle
     * @param string $activityId
     * @param string $workflowID
     */
    public function __construct(array $documents,string $category, string $documentTitle, string $activityId, string $workflowID, bool $automaticExecution)
    {
        $this->rows = $documents;
        $this->documentTitle = $documentTitle;
        $this->category = $category;
        $this->workflowId = $workflowID;
        $this->activityId = $activityId;
        $this->automaticExecution = $automaticExecution;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ids_created = [];
        $results = [];
        foreach ($this->rows as $key => $attribute) {
            $created = $this->newDocument($this->category,1,$attribute,$this->documentTitle);
            if($created->success == true)
            {
                array_push($ids_created, $created->iddocument);
            }
            array_push($results,$created);

        }
        Log::channel('se_documents')->info($ids_created);

        $association = null;
        if($ids_created && $this->activityId )
        {
            $association = $this->newAssocDocumentMultiple($ids_created,$this->workflowId,$this->activityId);
            Log::channel('se_documents')->info($association);

            if($this->automaticExecution){
                $this->executeActivity($this->workflowId,$this->activityId);
            }

        }



    }

}
