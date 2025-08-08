<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormRequst;
use Illuminate\Support\Facades\Http;

class FormController extends Controller
{


    public function index()
    {
        $lastRequest = FormRequst::latest()->first();
        
        if ($lastRequest) {
            // Маскируем данные
            $maskedPhone = $this->maskPhone($lastRequest->phone);
            $maskedName = $this->maskName($lastRequest->name);
            $maskedComment = $this->maskComment($lastRequest->comment);
            
            $lastRequest->masked_phone = $maskedPhone;
            $lastRequest->masked_name = $maskedName;
            $lastRequest->masked_comment = $maskedComment;
        }
        
        return view('index', compact('lastRequest'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        // Очищаем телефон от всех символов, оставляем только цифры
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        // Если номер начинается с 8, заменяем на 7
        if (strlen($phone) == 11 && substr($phone, 0, 1) == '8') {
            $phone = '7' . substr($phone, 1);
        }
        
        // Если номер 10 цифр, добавляем 7 в начало
        if (strlen($phone) == 10) {
            $phone = '7' . $phone;
        }

        $data = FormRequst::create([
            'name' => $request->name,
            'phone' => $phone,
            'comment' => $request->comment
        ]);

        // if($data){
        //     $response = Http::withHeaders([
        //         'Authorization' => config('app.crm_api_key'),
        //         'Content-Type' => 'application/json'
        //     ])->post(config('app.crm_api_url'), [
        //         "phone" => $phone,
        //         "name_first" => $request->name,
        //         "name_last" => "",
        //         "name_middle" => "",
        //         "source_id" => $data->id,
        //         "entry_point" => config('app.crm_api_entry_point'),
        //         "comment" => $request->comment
        //     ]);
        // }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Форма успешно отправлена']);
        }

        return redirect()->back()->with('success', 'Форма успешно отправлена');
    }
    
    private function maskPhone($phone)
    {
        // Телефон уже в формате 79999999999, оставляем только последние 4 цифры
        if (strlen($phone) >= 4) {
            $lastFour = substr($phone, -4);
            $masked = str_repeat('*', strlen($phone) - 4) . $lastFour;
            
            // Форматируем телефон правильно: +7 (***) ***-**-1234
            // Для номера 79991234567: masked = "*******4567"
            return '+7 (' . substr($masked, 0, 3) . ') ' . substr($masked, 3, 3) . '-' . substr($masked, 6, 2) . '-' . substr($masked, 8, 4);
        }
        return $phone;
    }
    
    private function maskName($name)
    {
        $name = trim($name);
        if (strlen($name) <= 2) {
            return $name;
        }
        
        $first = mb_substr($name, 0, 1, 'UTF-8');
        $last = mb_substr($name, -1, 1, 'UTF-8');
        $middle = str_repeat('*', mb_strlen($name, 'UTF-8') - 2);
        
        return $first . $middle . $last;
    }
    
    private function maskComment($comment)
    {
        if (empty($comment)) {
            return 'Комментарий отсутствует';
        }
        
        // Если комментарий длинный, обрезаем его
        if (strlen($comment) > 50) {
            return substr($comment, 0, 47) . '...';
        }
        
        return $comment;
    }
}
