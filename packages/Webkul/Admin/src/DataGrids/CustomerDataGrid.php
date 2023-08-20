<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CustomerDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'customer_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {

        $queryBuilder = DB::table('customers')
            ->leftJoin('addresses', 'customers.id', '=', 'addresses.customer_id')
            ->where('addresses.address_type', 'customer')
            ->addSelect(DB::raw('COUNT(addresses.id) as address_count'))

            // ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            // ->addSelect(DB::raw('COUNT(orders.id) as orders_count'))

            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->addSelect(
                'customers.id as customer_id',
                'customers.email',
                'customers.phone',
                'customers.gender',
                'customers.status',
                'customers.is_suspended',
                'customer_groups.name as group',
            )
            ->addSelect(
                DB::raw('CONCAT(' . DB::getTablePrefix() . 'customers.first_name, " ", ' . DB::getTablePrefix() . 'customers.last_name) as full_name')
            );

        $this->addFilter('customer_id', 'customers.id');
        // $this->addFilter('full_name', DB::raw('CONCAT(' . DB::getTablePrefix() . 'customers.first_name, " ", ' . DB::getTablePrefix() . 'customers.last_name)'));
        $this->addFilter('group', 'customer_groups.name');
        $this->addFilter('phone', 'customers.phone');
        $this->addFilter('gender', 'customers.gender');
        $this->addFilter('status', 'status');
        $this->addFilter('is_suspended', 'customers.is_suspended');

        // dd($queryBuilder->get());
        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'customer_id',
            'label'      => trans('admin::app.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('admin::app.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'group',
            'label'      => trans('admin::app.datagrid.group'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('admin::app.datagrid.phone'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                if (! $row->phone) {
                    return '-';
                }

                return $row->phone;
            },
        ]);

        $this->addColumn([
            'index'      => 'gender',
            'label'      => trans('admin::app.datagrid.gender'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                if (! $row->gender) {
                    return '-';
                }

                return $row->gender;
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.datagrid.status'),
            'type'       => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $html = '';

                if ($row->status) {
                    $html .= '<span class="badge badge-md badge-success">' . trans('admin::app.customers.customers.active') . '</span>';
                } else {
                    $html .= '<span class="badge badge-md badge-danger">' . trans('admin::app.customers.customers.inactive') . '</span>';
                }

                if ($row->is_suspended) {
                    $html .= '<span class="badge badge-md badge-danger">' . trans('admin::app.datagrid.suspended') . '</span>';
                }

                return $html;
            },
        ]);

        $this->addColumn([
            'index'       => 'is_suspended',
            'label'       => trans('admin::app.datagrid.suspended'),
            'type'        => 'boolean',
            'searchable'  => false,
            'filterable'  => true,
            'visibility'  => false,
            'sortable'    => true,
        ]);

        $this->addColumn([
            'index'       => 'address_count',
            'label'       => trans('Address Count'),
            'type'        => 'integer',
            'searchable'  => false,
            'filterable'  => true,
            'visibility'  => false,
            'sortable'    => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-view',
            'title'  => trans('admin::app.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.customer.view', $row->customer_id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-exit',
            'title'  => trans('admin::app.datagrid.login-as-customer'),
            'method' => 'GET',
            'target' => 'blank',
            'url'    => function ($row) {
                return route('admin.customer.login_as_customer', $row->customer_id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('admin::app.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.customer.delete', $row->customer_id);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'title'  => trans('admin::app.datagrid.delete'),
            'method' => 'POST',
            'action' => route('admin.customer.mass_delete'),
        ]);

        $this->addMassAction([
            'title'   => trans('admin::app.datagrid.update-status'),
            'method'  => 'POST',
            'action'  => route('admin.customer.mass_update'),
            'options' => [
                trans('admin::app.datagrid.active')    => 1,
                trans('admin::app.datagrid.inactive')  => 0,
            ],
        ]);
    }
}
