<?php

namespace WalkerChiu\Firewall\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class SettingFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'    => trans('php-firewall::setting.host_type'),
            'host_id'      => trans('php-firewall::setting.host_id'),
            'morph_type'   => trans('php-firewall::setting.morph_type'),
            'morph_id'     => trans('php-firewall::setting.morph_id'),
            'serial'       => trans('php-firewall::setting.serial'),
            'identifier'   => trans('php-firewall::setting.identifier'),
            'is_whitelist' => trans('php-firewall::setting.is_whitelist'),
            'is_enabled'   => trans('php-firewall::setting.is_enabled'),

            'name'        => trans('php-firewall::setting.name'),
            'description' => trans('php-firewall::setting.description')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'    => 'required_with:host_id|string',
            'host_id'      => 'required_with:host_type|integer|min:1',
            'morph_type'   => 'required_with:morph_id|string',
            'morph_id'     => 'required_with:morph_type|integer|min:1',
            'serial'       => 'nullable|string',
            'identifier'   => 'nullable|string',
            'is_whitelist' => 'boolean',
            'is_enabled'   => 'boolean',

            'name'        => 'required|string|max:255',
            'description' => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.firewall.settings').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'              => trans('php-core::validation.required'),
            'id.integer'               => trans('php-core::validation.integer'),
            'id.min'                   => trans('php-core::validation.min'),
            'id.exists'                => trans('php-core::validation.exists'),
            'host_type.required_with'  => trans('php-core::validation.required_with'),
            'host_type.string'         => trans('php-core::validation.string'),
            'host_id.required_with'    => trans('php-core::validation.required_with'),
            'host_id.integer'          => trans('php-core::validation.integer'),
            'host_id.min'              => trans('php-core::validation.min'),
            'morph_type.required_with' => trans('php-core::validation.required_with'),
            'morph_type.string'        => trans('php-core::validation.string'),
            'morph_id.required_with'   => trans('php-core::validation.required_with'),
            'morph_id.integer'         => trans('php-core::validation.integer'),
            'morph_id.min'             => trans('php-core::validation.min'),
            'serial.string'            => trans('php-core::validation.string'),
            'identifier.string'        => trans('php-core::validation.string'),
            'is_whitelist.boolean'     => trans('php-core::validation.boolean'),
            'is_enabled.boolean'       => trans('php-core::validation.boolean'),

            'name.required' => trans('php-core::validation.required'),
            'name.string'   => trans('php-core::validation.string'),
            'name.max'      => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-firewall.onoff.site-mall')
                    && !empty(config('wk-core.class.site-mall.site'))
                    && $data['host_type'] == config('wk-core.class.site-mall.site')
                ) {
                    $result = DB::table(config('wk-core.table.site-mall.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-firewall.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['host_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.firewall.setting')::where('identifier', $data['identifier'])
                                ->when(isset($data['host_type']), function ($query) use ($data) {
                                    return $query->where('host_type', $data['host_type']);
                                  })
                                ->when(isset($data['host_id']), function ($query) use ($data) {
                                    return $query->where('host_id', $data['host_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-firewall::setting.identifier')]));
            }
        });
    }
}
