<div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">

    <h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1">
        {{ $title ?? pageTitle() }}
        <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span>
    </h1>

    <div class="d-flex align-items-center text-muted fw-bold fs-7">

        {!! breadcrumb($breadcrumbs ?? []) !!}
    </div>

</div>
