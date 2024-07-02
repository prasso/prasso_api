<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     title="Prasso",
 *     version="1.0.0",
 *     description="Prasso api endpoints",
 *      @OA\Contact(
 *          email="info@faxt.com"
 *      ),
 * )
 * @OAS\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="http",
 *      scheme="bearer"
 * )
 * @OA\Response(
 *      response="401",
 *      description="Unauthorized",
 * )
 */
class CommandController extends BaseController
{
    
    /**
     * Run an Artisan command
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/run-artisan-command",
     *     tags={"Artisan Commands"},
     *     summary="Run an Artisan command",
     *     description="Execute a specified Artisan command with optional arguments",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"command"},
     *             @OA\Property(property="command", type="string", example="update:master-page"),
     *             @OA\Property(property="arguments", type="array", @OA\Items(type="string"), example={"pageContentsId=1", "masterId=1"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Command executed successfully")
     *         )
     *     )
     * )
     */
    /*public function runCommand(Request $request)
    {
        $command = $request->input('command');
        $arguments = $request->input('arguments', []);

        try{
            Artisan::call($command, $arguments);
        }
        catch( \Throwable $e){
            Log::info($e);
        }
        // You can return a response or perform additional logic here
        return response()->json(['message' => 'Command executed successfully']);
    }*/

    public function runCommand(Request $request)
    {
        // verify super admin access

        $command = $request->input('command');
        $arguments = $request->input('arguments', []);

        try {
            $argumentsString = implode(' ', \Arr::flatten($arguments));

            // Concatenate the command and the arguments string
            $fullCommand = "$command $argumentsString";

            // Call Artisan command with the full command
            $exitCode = Artisan::call($fullCommand);

            // Get the output generated by the command
            $output = Artisan::output();


            // Log the command's output and exit code
            Log::info("Command: $command with arguments " . implode(' ', $arguments));
            Log::info("Exit Code: $exitCode");
            Log::info("Output: $output");
        } catch (\Throwable $e) {
            // Log any exceptions thrown during the execution of the command
            Log::error("Error executing command: $command");
            Log::error($e);
            return response()->json(['error' => 'An error occurred while executing the command'], 500);
        }

        // Return a response indicating the command executed successfully
        return response()->json(['message' => 'Command executed successfully', 'output' => $output]);
}
}