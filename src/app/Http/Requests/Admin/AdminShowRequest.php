<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                'clock_in' => ['nullable', 'date_format:H:i'],
                'clock_out' => ['nullable', 'date_format:H:i'],
                'break_start' => ['nullable', 'date_format:H:i'],
                'break_end' => ['nullable', 'date_format:H:i'],
                'break2_start' => ['nullable', 'date_format:H:i'],
                'break2_end' => ['nullable', 'date_format:H:i'],
                'remarks' => ['required', 'string', 'max:200'],
            ];
    }
    
    public function messages()
    {
        return [
                'remarks.required' => '備考を記入してください',
                'remarks.max' => '備考は200文字以内で入力してください',
            ];
    }
    
        
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clock_in = $this->clock_in ? Carbon::parse($this->clock_in) : null;
            $clock_out = $this->clock_out ? Carbon::parse($this->clock_out) : null;
    
            if ($clock_in && $clock_out && $clock_in > $clock_out) {
            $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }
    
            $breaks = [
                ['start' => $this->break_start, 'end' => $this->break_end, 'start_name' => 'break_start', 
                'end_name' => 'break_end'],
                ['start' => $this->break2_start, 'end' => $this->break2_end, 'start_name' => 'break2_start', 'end_name' => 'break2_end'],
                ];
    
            foreach ($breaks as $i => $break) {
                
                if ($break['start']) {
                $breakStart = Carbon::parse($break['start']);
                if (($clock_in && $breakStart < $clock_in) || ($clock_out && $breakStart > $clock_out)) {
                $validator->errors()->add
                ($break['start_name'], '休憩時間が不適切な値です');
                            //('break' . ($i + 1) . '_start', '休憩時間が不適切な値です');
                    }
                }
    
                if ($break['end']) {
                    $breakEnd = Carbon::parse($break['end']);
                    if ($clock_out && $breakEnd > $clock_out) {
                    $validator->errors()->add
                    ($break['end_name'], '休憩時間もしくは退勤時間が不適切な値です');
                            
                    }
                }
            }
        });
    }
}
