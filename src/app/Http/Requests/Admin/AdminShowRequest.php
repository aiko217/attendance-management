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
                'new_breaks' => ['nullable', 'array'],
                'new_breaks.*.in' => ['nullable', 'date_format:H:i'],
                'new_breaks.*.out' => ['nullable', 'date_format:H:i'],
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
            $clockIn = $this->clock_in ? Carbon::parse($this->clock_in) : null;
            $clockOut = $this->clock_out ? Carbon::parse($this->clock_out) : null;
    
            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
            $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }
    
            foreach ($this->input('new_breaks', []) as $index => $break) {
                if (empty($break['in']) && empty($break['out'])) {
                    continue;
                }
    
                $breakIn  = !empty($break['in'])  ? Carbon::parse($break['in'])  : null;
                $breakOut = !empty($break['out']) ? Carbon::parse($break['out']) : null;

                if ($breakIn) {
                    if (
                        ($clockIn && $breakIn->lt($clockIn)) || 
                        ($clockOut && $breakIn->gt($clockOut))
                    ) {
                    $validator->errors()->add(
                        "new_breaks.$index.in",
                        '休憩開始が不適切な値です'
                    );
                    }
                }
                if ($breakOut && $clockOut && $breakOut->gt($clockOut)) {
                    $validator->errors()->add(
                        "new_breaks.$index.out",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
    
        });
    }
}
