

<x-dashboard.views::layouts.dashboards.default-dashboard>

    @php
        $statusCompletedId = App\Modules\Core\Models\Status::where("name", "completed")->first()?->id ?? 0;
        $statusInProgressId = App\Modules\Core\Models\Status::where("name", "in_progress")->first()?->id ?? 0;
        $selectedProcessId = $selectedProcessId ?? 0;
        $timeDurationLabel = ucfirst(str_replace('_', ' ', $timeDuration));
    @endphp



    <x-slot name="mainTitle">
        <strong class="text-info text-gradient">{{ $selectedProcessName?? ''}} </strong> Overview
    </x-slot>

    <x-slot name="subtitle">
        <span class="text-primary text-xs fst-italic">{{ $timeDuration !== 'custom' ? ucfirst(str_replace('_', ' ', $timeDuration)) : $fromTime . ' to ' . $toTime }}</span>
    </x-slot>

    <x-slot name="controls">
        @include('dashboard.views::components.layouts.dashboards.dashboard-control')
    </x-slot>

    {{------ DASHBOAD BODY CONTENT --------}}
   

    {{---- Top rows ----}}
    <div class="row g-4 mb-4">
        
        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Modules\Access\Models\Role"
                recordName="Total Available Roles"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-users text-lg opacity-10"
                key="icon-card-total-users"
            />
        </div>
        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Models\User"
                recordName="Roles without Permissions"
                :pivotTable="'model_has_roles'"
                pivotModelColumn="model_id"
                pivotModelType="App\\Models\\User"
                pivotRelatedColumn="role_id"
                :pivotRelatedColumnIn="App\Modules\Access\Models\Role::doesntHave('permissions')->pluck('id')->toArray()"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                showRecordNameOnly="true"
                iconCSSClass="fas fa-user-lock text-lg opacity-10"
                key="icon-card-roles-no-permission"
                :timeDuration="$timeDuration"
            />
        </div>
        

        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Modules\Access\Models\Role"
                recordName="Newly Created Roles"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-user-plus text-lg opacity-10"
                key="icon-card-recent-users"
            />
        </div>        

        
    </div>
   {{-- <div class="row g-4 mb-4">
        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Models\EployeeProfile"
                recordName="Total Employees"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-users text-lg opacity-10"
                key="icon-card-total-users"
            />
        </div>
        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Models\CustomerProfile"
                recordName="Total Customers"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-user-check text-lg opacity-10"
                key="icon-card-active-users"
            />
        </div>

        <div class="col-12 col-sm-4">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Models\SuplierProfile"
                recordName="Total Suppliers"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-user-plus text-lg opacity-10"
                key="icon-card-recent-users"
            />
        </div>

     
    </div>--}}


    {{---- Second row ----}}
    <div class="row g-4 mb-4">


        <div class="col-12">
            <livewire:dashboard.visualisation.widgets.charts.chart
                recordModel="App\Models\User"
                chartType="bar"
                recordName="Users"
                title="Users by Roles"
                showRecordNameOnly="true"
                column="name"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                canvasHeight=125
                :controls="['chartType']"
                key="chart-user-by-role"

                pivotTable="model_has_roles"
                pivotModelColumn="model_id"
                pivotRelatedColumn="role_id"
                pivotModelType="App\Models\User"
                groupByTable="roles"
                groupByTableColumn="name"
            />
        </div>
        
    </div>


    {{---- Table row ----}}
    <div class="row g-4 mb-4">

        <livewire:data-tables.data-table-manager model="App\Modules\Access\Models\Role"
                pageTitle="User Role Overview"
                queryFilters=[]
                :hiddenFields="[
                   
                ]"
            />
    </div>






    {{---

    <livewire:dashboard.visualisation.widgets.counters.count-up count-to="23980" />

    @php
        $duration = "+10 year";
    @endphp
    <livewire:dashboard.visualisation.widgets.counters.count-down :end-time="strtotime($duration)" />


    <livewire:dashboard.visualisation.widgets.steppers.stepper-wizard
        :steps="[
            ['label' => 'Step 1', 'content' => '<p>Content for Step 1</p>'],
            ['label' => 'Step 2', 'content' => '<p>Content for Step 2</p>'],
            ['label' => 'Step 3', 'content' => '<p>Content for Step 3</p>'],
            ['label' => 'Step 4', 'content' => '<p>Content for Step 3</p>'],
            ['label' => 'Step 5', 'content' => '<p>Content for Step 3</p>'],
        ]"
    />


--}}

</x-dashboard.views::layouts.dashboards.default-dashboard>









































