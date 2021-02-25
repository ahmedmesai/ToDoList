<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\api\BaseController as BaseController;
use App\Http\Resources\Task as ResourcesTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{
    // Show All Tasks
    // public function index()
    // {
    //     $user = Auth::user();
    //     $tasks = $user->tasks()->latest()->get();

    //     return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd Tasks Successfully');
    // }


    public function changeStatusTask($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                if ($task->is_completed == false) {
                    $task->is_completed = true;
                } else {
                    $task->is_completed = false;
                }
                $task->save();
                return $this->sendResponse(new ResourcesTask($task), 'Task Showed After Change Status Successfully');
            } else {
                return $this->sendError('You do not have right to change status this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    public function ongoingTasks()
    {
        $user = Auth::user();
        $tomorrow = Carbon::today()->addDay();
        // $tasks = $user->tasks()->where('is_completed', false)->latest()->get();
        $tasks = $user->tasks()->where('is_completed', false)
            ->where('task_date', '<', $tomorrow)->latest()->get();
        return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd All Tasks Not Completed Yet Successfully');
    }


    public function completedTasks()
    {
        $user = Auth::user();
        $tomorrow = Carbon::today()->addDay();
        $yesterday = Carbon::today()->subDay();
        $tasks = $user->tasks()->where('is_completed', true)
            ->where('task_date', '<', $tomorrow)
            ->where('task_date', '>', $yesterday)->latest()->get();

        return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd All Tasks Completed Successfully');
    }

    public function goTaskTomorrow($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $today = Carbon::today();
                $task->task_date = Carbon::createFromFormat('Y-m-d h:i:s', $today)->addDay();
                $task->save();
                return $this->sendResponse(new ResourcesTask($task), 'Task Transfered Tomorrow Successfully');
            } else {
                return $this->sendError('You do not have right accessing this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    public function backTaskToday($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $tomorrow = Carbon::today()->addDay();
                $task->task_date = Carbon::createFromFormat('Y-m-d h:i:s', $tomorrow)->subDay();
                $task->save();
                return $this->sendResponse(new ResourcesTask($task), 'Task Transfered Today Successfully');
            } else {
                return $this->sendError('You do not have right accessing this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $input = $request->all();
        $input['user_id'] = Auth::id();

        $task = Task::create($input);
        return $this->sendResponse($task, 'Task Created Successfully');
    }


    public function show($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                return $this->sendResponse(new ResourcesTask($task), 'Task Showed Successfully');
            } else {
                return $this->sendError('You do not have right accessing this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            $validatar = Validator::make($request->all(), [
                'title' => 'required'
            ]);

            if ($validatar->fails()) {
                return $this->sendError('Validate Error', $validatar->errors());
            }

            if ($task->user_id === Auth::id()) {
                $task->title = $request->title;
                if ($request->has('content')) $task->content = $request->content;
                $task->save();
                return $this->sendResponse(new ResourcesTask($task), 'Task Updated Successfully');
            } else {
                return $this->sendError('You do not have right updating this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    public function destroy($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $task->delete();
                return $this->sendResponse(new ResourcesTask($task), 'Task Deleted Successfully');
            } else {
                return $this->sendError('You do not have right deleting this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }
}
