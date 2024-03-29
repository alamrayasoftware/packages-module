<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Models\MItem;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseFormatter;
use ArsoftModules\NotaGenerator\Facades\NotaGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public $responseFormatter = null;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }

    public function index(Request $request)
    {
        $listCompanies = MItem::orderBy('name')->get();

        $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
        return $this->responseFormatter->successResponse('', $listCompanies);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $code = $request->code ?? NotaGenerator::generate('m_items', 'code', 5)->addPrefix('ITEM', '/')->getResult();
            
            $newData = new MItem();
            $newData->code = $code;
            $newData->name = $request->name;
            $newData->type = $request->type;
            $newData->status = $request->status;
            $newData->cogs = $request->cogs;
            $newData->default_selling_price = $request->default_selling_price;
            $newData->minimum_stock_qty = $request->minimum_stock_qty;
            $newData->note = $request->note;
            $newData->save();

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $data = MItem::findOrFail($id);

            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = MItem::findOrFail($id);
            $data->code = $request->code;
            $data->name = $request->name;
            $data->type = $request->type;
            $data->status = $request->status;
            $data->cogs = $request->cogs;
            $data->default_selling_price = $request->default_selling_price;
            $data->minimum_stock_qty = $request->minimum_stock_qty;
            $data->note = $request->note;
            $data->save();

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            MItem::destroy($id);

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}