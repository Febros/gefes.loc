<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*Model access*/
use App\Page;
use App\People;
use App\Portfolio;
use App\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class IndexController extends Controller
{
    public function execute(Request $request)
    {
        /*Проверка типа запроса от пользователя*/
        if($request->isMethod('post'))
        {
            $messages =
                [
                    'required' => "Поле обязательно к заполнению",
                    'email' => "Поле :attribute должно соответствовать указанному адресу"
                ];
            $this->validate($request,
                [
                    'name' => 'required|max:255',
                    'email' => 'required|email',
                    'text' => 'required'
                ],   $messages);

            $data = $request->all(); //Все содержимаое массива request помещается в data

            $result = Mail::send('site.email', ['data'=>$data], function ($message) use ($data){
                $mail_admin = env('MAIL_ADMIN');
                $message->from($data['email'],$data['name']);
                $message->to($mail_admin)->subject('Question');
            });
            if (is($result))
            {
                return redirect()->route('home')->with('status','Сообщение успешно отправлено');
            }
        }

        $pages = Page::all(); //Выбор всех записей из таблицы Page
        $peoples = People::take(3)->get(); //Или получить только первые 3 поля
        $portfolios = Portfolio::get(array('name','filters','images')); //Или выбрать и получить конкретные поля таблицы
        $services = Service::where('id', '<', 20)->get(); //Или выбрать поля через условие

        $tags = DB::table('portfolios')->distinct()->pluck('filters'); //distinct - выбирает только уникальные поля из таблицы

        $menu = array();
        foreach ($pages as $page)
        {
            $item = array('title'=>$page->name, 'alias'=>$page->alias);
            array_push($menu,$item);
        }

        $item = array('title'=>'Услуги','alias'=>'service');
        array_push($menu,$item);

        $item = array('title'=>'Работы','alias'=>'Portfolio');
        array_push($menu,$item);

        $item = array('title'=>'Команда','alias'=>'team');
        array_push($menu,$item);

        $item = array('title'=>'Контакт','alias'=>'contact');
        array_push($menu,$item);

        return view('site.index', array
                                  (
                                  'menu'=>$menu,
                                  'pages'=>$pages,
                                  'services'=>$services,
                                  'portfolios'=>$portfolios,
                                  'peoples'=>$peoples,
                                  'tags'=>$tags
                                  ));
    }
}
