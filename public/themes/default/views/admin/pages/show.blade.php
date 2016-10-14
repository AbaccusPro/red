<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			{{ trans('common.manage_pages') }}
		</h3>
	</div>
	<div class="panel-body timeline">
		@include('flash::message')
		@if(count($pages) > 0)
			<div class="table-responsive manage-table">
				<table class="table existing-products-table socialite">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th>{{ trans('admin.id') }}</th> 
							<th>{{ trans('auth.name') }}</th>
							<th>{{ trans('common.likes') }}</th> 
							<th>{{ trans('admin.options') }}</th> 
							<th>&nbsp;</th> 
						</tr>
					</thead>
					<tbody>
						@foreach($pages as $page)
						<tr>
							<td>&nbsp;</td>	
							<td>{{ $page->id }}</td>
							<td><a href="#"><img src="@if($page->avatar) {{ url('page/avatar/'.$page->avatar) }} @else {{ url('page/avatar/default-page-avatar.png') }} @endif" alt="{{ $page->timeline->name }}" title="{{ $page->timeline->name }}"></a><a href="{{ url($page->timeline->username) }}"> {{ $page->timeline->name }}</a></td>
							<td>{{ $page->likes->count() }}</td> 
							<td>
								<ul class="list-inline">
									<li><a href="{{ url('admin/pages/'.$page->timeline->username.'/edit')}}"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="{{ url('admin/pages/'.$page->id.'/delete')}}" onclick="return confirm('{{ trans("messages.are_you_sure") }}')"><span class="trash-icon bg-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></a></li>
								</ul>
							</td>
							<td>&nbsp;</td> 
						</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				<div class="pagination-holder">
					{{ $pages->render() }}
				</div>	
			@else
				<div class="alert alert-warning">{{ trans('messages.no_pages') }}</div>
			@endif
		</div>
	</div>
