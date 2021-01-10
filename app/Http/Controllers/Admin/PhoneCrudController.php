<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PhoneRequest;
use App\Models\Phone;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class PhoneCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PhoneCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation {
        search as searchFromTrait;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Phone::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/phone');
        CRUD::setEntityNameStrings('телефон', 'телефоны');
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

//        $totalRows = $this->crud->model->count();
//        $filteredRows = $this->crud->query->toBase()->getCountForPagination();

        $raw = DB::select('EXPLAIN SELECT COUNT(id) FROM `phones`.`phones` USE INDEX (PRIMARY)');
        $totalRows = $raw[0]->rows;
        $filteredRows = $totalRows;
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm(request()->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            $this->crud->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            $this->crud->take((int) request()->input('length'));
        }
        // overwrite any order set in the setup() method with the datatables order
        if (request()->input('order')) {
            $column_number = request()->input('order')[0]['column'];
            $column_direction = request()->input('order')[0]['dir'];
            $column = $this->crud->findColumnById($column_number);
            if ($column['tableColumn']) {
                // clear any past orderBy rules
                $this->crud->query->getQuery()->orders = null;
                // apply the current orderBy rules
                $this->crud->orderByWithPrefix($column['name'], $column_direction);
            }

            // check for custom order logic in the column definition
            if (isset($column['orderLogic'])) {
                $this->crud->customOrderBy($column, $column_direction);
            }
        }

        // show newest items first, by default (if no order has been set for the primary column)
        // if there was no order set, this will be the only one
        // if there was an order set, this will be the last one (after all others were applied)
        $orderBy = $this->crud->query->getQuery()->orders;
        $hasOrderByPrimaryKey = false;
        collect($orderBy)->each(function ($item, $key) use ($hasOrderByPrimaryKey) {
            if (! isset($item['column'])) {
                return false;
            }

            if ($item['column'] == $this->crud->model->getKeyName()) {
                $hasOrderByPrimaryKey = true;

                return false;
            }
        });
        if (! $hasOrderByPrimaryKey) {
            $this->crud->orderByWithPrefix($this->crud->model->getKeyName(), 'DESC');
        }

        $entries = $this->crud->getEntries();

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

//        CRUD::setFromDb(); // columns


        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

//        $this->crud->setColumns(['vc_phone', 'vc_fio']);
        $this->crud->addColumn([
            'name'  => 'vc_phone',
            'type'  => 'text',
            'label' => 'Телефон',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('vc_phone', 'like', $searchTerm.'%');
            }
        ]);
        $this->crud->addColumn([
            'name'  => 'vc_fio',
            'type'  => 'text',
            'label' => 'Ф.И.О.',
//            'searchLogic' => false,
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('vc_fio', 'like', $searchTerm.'%');
            }
        ]);
        $this->crud->addColumn([
            'name'  => 'dt_born',
            'type'  => 'date',
            'label' => 'Дата рождения',
//            'visibleInTable' => false,
//            'searchLogic' => function ($query, $column, $searchTerm) {
//                $query;
//            }
            'searchLogic' => false
        ]);
//        $this->crud->addColumn([
//            'name' => 'sex_id',
//            'type' => 'select',
//            'label' => "Пол",
//            'entity'    => 'sex', // the method that defines the relationship in your Model
//            'attribute' => 'vc_name', // foreign key attribute that is shown to user
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_region',
//            'type' => 'text',
//            'label' => "Регион",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//
//        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_city',
//            'type' => 'text',
//            'label' => "Город",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'tx_location',
//            'type' => 'text',
//            'label' => "Адрес",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_email',
//            'type' => 'email',
//            'label' => "Email",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_link',
//            'type' => 'url',
//            'label' => "Ссылка",
////            'visibleInTable'  => false, // no point, since it's a large text
////            'visibleInModal'  => false, // would make the modal too big
////            'visibleInExport' => false, // not important enough
////            'visibleInShow'   => true, // sure, why not
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_source',
//            'type' => 'text',
//            'label' => "Источник",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'dt_rec',
//            'type' => 'date',
//            'label' => "Дата записи",
////            'visibleInTable' => false,
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);
//        $this->crud->addColumn([
//            'name' => 'tx_rem',
//            'type' => 'text',
//            'label' => "Примечание",
////            'searchLogic' => function ($query, $column, $searchTerm) {
////                $query;
////            }
//        ]);

        $this->crud->enableDetailsRow();
        $this->crud->setDetailsRowView('vendor.backpack.crud.details_row.phone');

//        $this->crud->enableExportButtons();
    }

    public function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
//        $this->setupListOperation();

//        $this->crud->set('show.contentClass', 'col-md-12');

        $this->crud->addColumn([
            'name' => 'vc_phone',
            'type' => 'phone',
            'label' => "Телефон"
        ]);
        $this->crud->addColumn([
            'name' => 'vc_fio',
            'type' => 'text',
            'label' => "Ф.И.О."
        ]);
        $this->crud->addColumn([
            'name' => 'dt_born',
            'type' => 'date',
//            'format' => 'l j F Y',
            'label' => "Дата рождения"
        ]);
        $this->crud->addColumn([
            'name' => 'sex_id',
            'type' => 'select',
            'label' => "Пол",
            'entity'    => 'sex', // the method that defines the relationship in your Model
            'attribute' => 'vc_name', // foreign key attribute that is shown to user
        ]);
        $this->crud->addColumn([
            'name' => 'vc_region',
            'type' => 'text',
            'label' => "Регион"
        ]);
        $this->crud->addColumn([
            'name' => 'vc_city',
            'type' => 'text',
            'label' => "Город"
        ]);
        $this->crud->addColumn([
            'name' => 'tx_location',
            'type' => 'textarea',
            'label' => "Адрес"
        ]);
        $this->crud->addColumn([
            'name' => 'vc_email',
            'type' => 'email',
            'label' => "Email"
        ]);
//        $this->crud->addColumn([
//            'name' => 'vc_source',
//            'type' => 'text',
//            'label' => "Источник"
//        ]);
        $this->crud->addColumn([
            'name' => 'source_id',
            'type' => 'select',
            'label' => "Источник",
            'entity'    => 'source', // the method that defines the relationship in your Model
            'attribute' => 'vc_name', // foreign key attribute that is shown to user
        ]);
        $this->crud->addColumn([
            'name' => 'dt_rec',
            'type' => 'date',
            'label' => "Дата записи"
        ]);
        $this->crud->addColumn([
            'name' => 'vc_link',
            'type' => 'url',
            'label' => "Ссылка"
        ]);
        $this->crud->addColumn([
            'name' => 'tx_rem',
            'type' => 'textarea',
            'label' => "Примечание"
        ]);
//        $this->crud->addColumn([
//            'name' => 'bn_hash',
//            'type' => 'text',
//            'label' => "Hash"
//        ]);
        $this->crud->removeColumn('bn_hash');
//        $this->crud->removeColumn('vc_source');


    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PhoneRequest::class);

//        $this->crud->setTitle('some string', 'create');
//        CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */

        $this->crud->addField([
            'name'    => 'vc_phone',
            'type'    => 'text',
            'label'   => "Телефон",
            'hint'    => 'Телефон в формате XXXXXXXXXX (десять цифр, без +7 или 8)',
            'attributes' => [
                'pattern' => '[0-9]{10}',
            ]
        ]);
        $this->crud->addField([
            'name' => 'vc_fio',
            'type' => 'text',
            'label' => "Ф.И.О."
        ]);
        $this->crud->addField([
            'name' => 'dt_born',
            'type' => 'date_picker',
            'date_picker_options' => [
                'format' => 'dd.mm.yyyy',
//                'format' => 'DD.MM.YYYY',
                'language' => 'ru'
            ],
            'label' => "Дата рождения"
        ]);
        $this->crud->addField([
            'name' => 'sex_id',
            'type' => 'select2',
            'label' => "Пол",
            'entity'    => 'sex', // the method that defines the relationship in your Model
            'attribute' => 'vc_name', // foreign key attribute that is shown to user
        ]);
        $this->crud->addField([
            'name' => 'vc_region',
            'type' => 'text',
            'label' => "Регион"
        ]);
        $this->crud->addField([
            'name' => 'vc_city',
            'type' => 'text',
            'label' => "Город"
        ]);
        $this->crud->addField([
            'name' => 'tx_location',
            'type' => 'textarea',
            'label' => "Адрес"
        ]);
        $this->crud->addField([
            'name' => 'vc_email',
            'type' => 'email',
            'label' => "Email"
        ]);
//        $this->crud->addField([
//            'name' => 'vc_source',
//            'type' => 'text',
//            'label' => "Источник"
//        ]);
        $this->crud->addField([
            'name' => 'source_id',
            'type' => 'select2',
            'label' => "Источник",
            'entity'    => 'source', // the method that defines the relationship in your Model
            'attribute' => 'vc_name', // foreign key attribute that is shown to user
        ]);
        $this->crud->addField([
            'name' => 'dt_rec',
            'type' => 'date_picker',
            'date_picker_options' => [
                'format' => 'dd.mm.yyyy',
//                'format' => 'DD.MM.YYYY',
                'language' => 'ru'
            ],
            'label' => "Дата записи"
        ]);
        $this->crud->addField([
            'name' => 'vc_link',
            'type' => 'url',
            'label' => "Ссылка"
        ]);
        $this->crud->addField([
            'name' => 'tx_rem',
            'type' => 'textarea',
            'label' => "Примечание"
        ]);
        $this->crud->addField([
            'name' => 'bn_hash',
            'type' => 'hidden',
            'label' => "Хэш"
        ]);

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function check($phone)
    {
        $req = Phone::where('vc_phone',$phone)->get()->toArray();
//dd($req);
        for($i=0;$i<count($req);$i++) {
            unset($req[$i]['bn_hash']);
        }
//        dd($req);
        echo json_encode($req);
    }

    public function update()
    {
        // do something before validation, before save, before everything; for example:
        // $this->crud->addField(['type' => 'hidden', 'name' => 'author_id']);
        // $this->crud->removeField('password_confirmation');

        $hash = hex2bin(md5($this->crud->getRequest()->vc_phone
            .$this->crud->getRequest()->vc_fio
            .$this->crud->getRequest()->dt_born
            .$this->crud->getRequest()->sex_id
            .$this->crud->getRequest()->vc_region
            .$this->crud->getRequest()->vc_city
            .$this->crud->getRequest()->tx_location
            .$this->crud->getRequest()->vc_email
            .$this->crud->getRequest()->vc_link
        ));
        $id = $this->crud->getRequest()->id;
        $exist = Phone::where('id','<>',$id)->where('bn_hash',$hash)->count();
        if (!$exist) {
            $this->crud->getRequest()->request->add(['bn_hash' => $hash]);
            $response = $this->traitUpdate();
            // do something after save
            return $response;
        } else {
            return redirect()->back()->withErrors('Запись уже существует.')->withInput();
        }

    }

    public function store()
    {
        // do something before validation, before save, before everything; for example:
        // $this->crud->addField(['type' => 'hidden', 'name' => 'author_id']);
        // $this->crud->removeField('password_confirmation');

        $hash = hex2bin(md5($this->crud->getRequest()->vc_phone
            .$this->crud->getRequest()->vc_fio
            .$this->crud->getRequest()->dt_born
            .$this->crud->getRequest()->sex_id
            .$this->crud->getRequest()->vc_region
            .$this->crud->getRequest()->vc_city
            .$this->crud->getRequest()->tx_location
            .$this->crud->getRequest()->vc_email
            .$this->crud->getRequest()->vc_link
        ));

        $exist = Phone::where('bn_hash',$hash)->count();
        if (!$exist) {
            $this->crud->getRequest()->request->add(['bn_hash' => $hash]);
            $response = $this->traitStore();
            // do something after save
            return $response;
        } else {
            return redirect()->back()->withErrors('Запись уже существует.')->withInput();
        }
    }
}
