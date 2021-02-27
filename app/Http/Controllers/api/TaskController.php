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

    // Function Change Status is Completed or Not
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
                return $this->sendResponse($task->is_completed == true ? 'Task Completed' : 'Task Not Completed Yet', 'Task Showed After Change Status Successfully');
            } else {
                return $this->sendError('You do not have right to change status this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    // Function OnGoing Tasks Today
    public function ongoingTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = $request->today;

        $user = Auth::user();
        $startOfDay = Carbon::create($today)->startOfDay();
        $endOfDay = Carbon::create($today)->endOfDay();

        // Transfer Not Completed Task To Next Day
        $notCompleteTasks = $user->tasks()->where('is_completed', false)->where('task_date', '<', $startOfDay)->get();
        if (count($notCompleteTasks) > 0) {
            foreach ($notCompleteTasks as $task) {
                $task->task_date = Carbon::create($today);
                $task->save();
            }
        }

        // All Tasks Not Completed Yet
        $tasks = $user->tasks()->where('is_completed', false)
            ->where('task_date', '<=', $endOfDay)
            ->where('task_date', '>=', $startOfDay)->latest()->get();
        return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd All Tasks Not Completed Yet Successfully');
    }


    // Function Completed Tasks Today or Any Day
    public function completedTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = $request->today;

        $user = Auth::user();
        $startOfDay = Carbon::create($today)->startOfDay();
        $endOfDay = Carbon::create($today)->endOfDay();
        $tasks = $user->tasks()->where('is_completed', true)
            ->where('task_date', '<=', $endOfDay)
            ->where('task_date', '>=', $startOfDay)->latest()->get();

        return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd All Tasks Completed Successfully');
    }


    // Tomorrow Tasks
    public function tomorrowTasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = $request->today;

        $user = Auth::user();
        $endOfDay = Carbon::create($today)->endOfDay();
        $tasks = $user->tasks()->where('is_completed', false)
            ->where('task_date', '>', $endOfDay)->latest()->get();
        return $this->sendResponse(ResourcesTask::collection($tasks), 'Retriverd All Tasks Tomorrow Not Completed Yet Successfully');
    }


    // Go Task Tomorrow
    public function goTaskTomorrow(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = $request->today;

        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $task->task_date = Carbon::create($today)->addDay();
                $task->save();
                return $this->sendResponse([], 'Task Transfered Tomorrow Successfully');
            } else {
                return $this->sendError('You do not have right accessing this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    // Back Task Today
    public function backTaskToday(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = $request->today;

        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $task->task_date = Carbon::create($today);
                $task->save();
                return $this->sendResponse([], 'Task Transfered Back Today Successfully');
            } else {
                return $this->sendError('You do not have right accessing this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    // Create Task
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'today' => 'required|date_format:d-m-Y'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validate Error', $validator->errors());
        }

        $today = Carbon::create($request->today);

        $input['user_id'] = Auth::id();
        $input['title'] = $request->title;
        if ($request->has('content')) $input['content'] = $request->content;
        $input['task_date'] = $today;
        $input['is_completed'] = false;

        $task = Task::create($input);
        return $this->sendResponse(new ResourcesTask($task), 'Task Created Successfully');
    }


    // Show Task
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


    // Update Task
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
                return $this->sendResponse([], 'Task Updated Successfully');
            } else {
                return $this->sendError('You do not have right updating this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }


    // Delete Task
    public function destroy($id)
    {
        $task = Task::find($id);
        if (!is_null($task)) {
            if ($task->user_id === Auth::id()) {
                $task->delete();
                return $this->sendResponse([], 'Task Deleted Successfully');
            } else {
                return $this->sendError('You do not have right deleting this Task');
            }
        } else {
            return $this->sendError('Can Not Found This Task');
        }
    }
}
