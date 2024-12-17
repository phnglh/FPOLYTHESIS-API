<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class BaseRequest extends FormRequest
{
public function response(array $errors)
{
throw new HttpResponseException(response()->json([
'success' => false,
'errors' => [
'message' => $errors
]
], 422));
}
}
