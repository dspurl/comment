<?php

namespace App\Observers\GoodIndent;

use App\Models\v1\Comment;
use App\Models\v1\GoodIndent;
use App\Models\v1\User;
use App\Notifications\InvoicePaid;
use Illuminate\Http\Request;

/**
 * user evaluate notification
 * 自动评价处理
 * Class AutomaticEvaluateObserver
 * @package App\Observers\Comment
 */
class AutomaticEvaluateObserver
{
    protected $request;
    protected $route = [];
    protected $execute = false;

    public function __construct(Request $request)
    {
        if (!app()->runningInConsole()) {
            $this->request = $request;
            $path = explode("admin", $request->path());
            if (count($path) == 2) {
                $name = 'admin' . $path[1];
            } else {
                $path = explode("app", $request->path());
                $name = 'app' . $path[1];
            }
            if (collect($this->route)->contains($name)) {
                $this->execute = true;
            }
        }
    }

    public function updated(GoodIndent $goodIndent)
    {
        if(app()->runningInConsole() && $goodIndent->state == GoodIndent::GOOD_INDENT_STATE_HAVE_EVALUATION){
            foreach ($goodIndent->goodsList as $id=>$goodsList){
                $Comment = new Comment();
                $Comment->user_id = 0;
                $Comment->model_id = $goodsList['id'];
                $Comment->score = 5;
                $Comment->model_type = 'App\Models\v1\GoodIndentCommodity';
                $Comment->details = '系统默认好评';
                $Comment->state = Comment::COMMENT_STATE_PASS;
                $Comment->anonymity = Comment::COMMENT_ANONYMITY_NO;
                $Comment->save();
            }
        }
    }
}
