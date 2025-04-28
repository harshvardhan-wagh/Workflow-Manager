<?php

namespace WorkflowManager\Entities;

class RevokeCondition {
    private $targetStepId;  
    private $resumeStepId;  

    public function __construct($targetStepId, $resumeStepId) {
        $this->targetStepId = $targetStepId;
        $this->resumeStepId = $resumeStepId;
    }

    // Gets the step ID to which the workflow should revert upon revocation
    public function getTargetStepId() {
        return $this->targetStepId;
    }

    // Gets the step ID from which the workflow should resume after revocation
    public function getResumeStepId() {
        return $this->resumeStepId;
    }
}