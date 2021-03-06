<?php

namespace App\Api\V1\Controllers\Glazy;

use App\Api\V1\Repositories\CollectionMaterialRepository;

use App\Api\V1\Repositories\CollectionRepository;
use App\Models\Collection;
use App\Models\Material;
use Illuminate\Http\Request;

use Auth;

class CollectionMaterialController extends ApiBaseController
{
    protected $collectionMaterialRepository;

    public function __construct(CollectionMaterialRepository $collectionMaterialRepository)
    {
        $this->collectionMaterialRepository = $collectionMaterialRepository;
    }

    /*
     * Obtain a material's collections via the MaterialRepository getWithDetails function
    public function collections($material_id) {

        $materialRepository = new MaterialRepository();
        $material = $materialRepository->get($id);

        if (!$material) {
            return $this->respondNotFound('Material does not exist');
        }

        if (!Auth::guard()->user()->can('view', $material)) {
            return $this->respondUnauthorized('This material cannot be viewed by you.');
        }

        $collections = $this->collectionMaterialRepository->getByMaterialId($material_id);

    }
    */

    public function store(Request $request)
    {
        $collection_id = (int)$request->input('collectionId');
        $new_collection_name = $request->input('collectionName');
        $material_ids = $request->input('materialIds');
        if (!$material_ids) {
            $material_id = (int)$request->input('materialId');
            if ($material_id) {
                $material_ids = [$material_id];
            }
            else {
                // Neither an array of material IDs nor a single material ID were given
                return $this->respondInternalError('Request did not contain materials to bookmark.');
            }
        }

        $collection = null;

        if (!empty($new_collection_name)) {
            // Create a new collection using this name
            $collectionRepository = new CollectionRepository();
            $collection = $collectionRepository->create([ 'name' => $new_collection_name ]);
            $collection_id = $collection->id;
        }
        else {
            $collection = Collection::find($collection_id);
        }
        if (! $collection) {
            return $this->respondNotFound('Collection does not exist');
        }
        if (!Auth::guard()->user()->can('update', $collection)) {
            return $this->respondUnauthorized('This collection does not belong to you.');
        }

        $materials = Material::whereIn('id', $material_ids)->get();
        if (! $materials) {
            return $this->respondNotFound('Materials to add to Collection do not exist');
        }

        foreach ($materials as $material) {
            if (!Auth::guard()->user()->can('view', $material)) {
                return $this->respondUnauthorized('This material cannot be collected by you.');
            }
        }
        foreach ($materials as $material) {
            $this->collectionMaterialRepository->create([
                'collection_id' =>  $collection_id,
                'material_id' => $material->id
            ]);
        }

        return $this->respondCreated('Collection materials successfully added');
    }

    public function destroy(Request $request, $collection_id, $material_id)
    {
        $collection = Collection::find($collection_id);
        $material = Material::find($material_id);

        if (! $collection) {
            return $this->respondNotFound('Collection does not exist');
        }
        if (! $material) {
            return $this->respondNotFound('Material does not exist');
        }

        if (!Auth::guard()->user()->can('update', $collection)) {
            return $this->respondUnauthorized('This collection does not belong to you.');
        }

        $this->collectionMaterialRepository->destroy($collection_id, $material_id);

        return $this->respondDeleted('Collection Material deleted');
    }
}
