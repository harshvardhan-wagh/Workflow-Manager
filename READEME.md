# Workflow Manager

A lightweight **PHP Workflow Engine** that supports **linear** and **parallel step traversals** within a **workflow instance**.

- 🌟 Create Workflow Templates (Blueprints)  
- 🌟 Create Workflow Instances based on templates  
- 🌟 Traverse steps **linearly** or **in parallel**  
- 🌟 Add **dynamic user assignment**, **revocations**, **resume steps**

---

## 📂 Project Structure

| Folder | Description |
|:-------|:------------|
| `Entities/` | Core entities like `Workflow`, `WorkflowStep`, `WorkflowInstance`, `WorkflowInstanceStep`, and `RevokeCondition`. |
| `Services/` | Business logic like creating workflows, instances, and handling traversals. |
| `ApiControllers/` | API layer for external communication (REST endpoints). |
| `Helpers/` | Utility functions. |
| `routes/` | API route definitions. |
| `Config/` | Configuration files (optional). |

---

## 🚀 How It Works

1. **Create a Workflow**  
   Create a blueprint defining a sequence of steps.

2. **Create an Instance**  
   Instantiate a workflow when a real task starts.

3. **Traverse Steps**
   - **Linear Traverse:** Move one step at a time.
   - **Parallel Traverse:** Move multiple steps in parallel, depending on conditions like:
     - "Any one approval" → proceed
     - "All must approve" → proceed

---

## 🔗 Key Entities

| Entity | Purpose |
|:-------|:--------|
| `Workflow` | Blueprint definition containing steps |
| `WorkflowStep` | Step information inside the workflow |
| `WorkflowInstance` | Running instance based on workflow blueprint |
| `WorkflowInstanceStep` | Step inside a running instance, with status, dynamic assignment |

---

## ⚙️ API Usage

### 1. Create a Workflow

**Endpoint:** `POST /workflow/create`  
**Request Body:**
```json

{  
    "user": {
    "employee_id": "345412",
    "employee_email": "harswagh@cdac.in",
    "sla": "101012",
    "employee_name": "Harshvardhan Wagh",
    "designation": "PA",
    "fla": "101011",
    "role": "employee"
    },
    "parentWorkflowId":"",
    "workflowName": "Gate Pass",
    "workflowDescription": "For the Gate Pass",
    "workflow_steps": [
        {
        "position": "1",
        "step_user_role": "Employee",
        "stepDescription": "",
        "actions": ["submit", "cancel"],
        "requires_user_id": "true",
        "is_user_id_dynamic": "false",
        "targetStepPosition": "",
        "resumeStepPosition": "",
        "nextStepPosition":"2",
        "prevStepPosition":""
        },
        {
        "position": "2",
        "step_user_role": "Owner",
        "stepDescription": "",
        "actions": ["approve", "reject", "revoke"],
        "requires_user_id": "true",
        "is_user_id_dynamic": "false",
        "targetStepPosition": "1",
        "resumeStepPosition": "2",
        "nextStepPosition":"3",
        "prevStepPosition":"1"
        },
        {
        "position": "3",
        "step_user_role": "MMG Manager",
        "stepDescription": "",
        "actions": ["approve", "reject", "revoke"],
        "requires_user_id": "false",
        "is_user_id_dynamic": "false",
        "targetStepPosition": "1",
        "resumeStepPosition": "3",
        "nextStepPosition":"4",
        "prevStepPosition":"2"
        },
         {
        "position": "4",
        "step_user_role": "Final Approvar",
        "stepDescription": "",
        "actions": ["approve", "reject", "revoke"],
        "requires_user_id": "false",
        "is_user_id_dynamic": "true",
        "targetStepPosition": "1",
        "resumeStepPosition": "4",
        "nextStepPosition":"5",
        "prevStepPosition":"3"
        }
    ]
}

```

**Response:**
```json
{
    "status": "success",
    "workflow": {
        "status": "success",
        "workflow_id": "Wf_440",
        "version_id": "Wf_440_v1"
    }
}
```

---

### 3. Create a Workflow Instance

**Endpoint:** `POST /workflow-instance/create`  
**Request Body:**
```json
{
    "sla": "101035",
    "fla": "201025",
    "owner":"566565",
    "group_head": "300013",
    "HR": "40014",
    "workflow_id": "Wf_440",
    "user": {
    "employee_id": "345412",
    "employee_email": "harswagh@cdac.in",
    "sla": "101035",
    "employee_name": "Harshvardhan Wagh",
    "designation": "PA",
    "fla": "201025",
    "role": "employee"
    }

}
```

**Response:**
```json
{
    "status": "success",
    "workflow": {
        "status": "success",
        "workflow_instance_id": "Wfi_206",
        "workflow_instance_name": "Gate Pass",
        "Workflow_id": "Wf_440"
    }
}
```

---

### 4. Accept Step (Move to Next Step)

**Endpoint:** `POST /workflow-instance/accept-step`  
**Request Body:**
```json
{
    "workflow_instance_id": "Wfi_206",
    "userStepPosition": "2",
    "nextStepEmployeeId":"345414",
    "action":"approve",
    "user": {
        "employee_id": "345412",
        "employee_email": "harswagh@cdac.in",
        "sla": "101035",
        "employee_name": "Harshvardhan Wagh",
        "designation": "PA",
        "fla": "201025",
        "role": "employee"
     }
}
```

**Response:**
```json
{
    "status": "success",
    "result": {
        "status": "success",
        "message": "Workflow marked as completed.",
        "workflow_id": "Wf_440",
        "workflow_instance_id": "Wfi_206",
        "workflow_instance_name": "Gate Pass",
        "currentStage": "4"
    }
}
```

---

## 🧩 Example Workflow Structure

```
Leave Request Workflow
|
|- Manager Approval (Step 1)
|    (Parallel)
|- HR Approval (Step 2)
|
-> If either approved → Proceed to Director Approval (Step 3)
```

---

## 🖥️ Requirements

- PHP 7.4+
- Composer (for dependency management, optional)

---

## 📄 Installation

```bash
git clone https://github.com/yourname/workflow-manager.git
cd workflow-manager
composer install
```

Configure your API routes and point your server to `index.php`.

---

## 🧙‍♂️ Developer Notes

- Entities are **pure PHP classes** → easily extendable.
- Services **handle all business logic**.
- API Controllers are **thin** → delegate to services.
- Can be integrated easily into Laravel / Symfony if needed later.

---

