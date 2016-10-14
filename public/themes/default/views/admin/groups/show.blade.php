<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			{{ trans('common.manage_groups') }}
		</h3>
	</div>
	<div class="panel-body timeline">
		@include('flash::message')
		@if(count($groups) > 0)
			<div class="table-responsive manage-table">
				<table class="table existing-products-table socialite">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th>{{ trans('admin.id') }}</th> 
							<th>{{ trans('auth.name') }}</th>
							<th>{{ trans('common.type') }}</th>
							<th>{{ trans('common.members') }}</th> 
							<th>{{ trans('admin.options') }}</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@foreach($groups as $group)
						<tr>
							<td>&nbsp;</td>	
							<td>{{ $group->id }}</td>
							<td><a href="#"><img src="@if($group->avatar) {{ url('group/avatar/'.$group->avatar) }} @else {{ url('group/avatar/default-group-avatar.png') }} @endif" alt="{{ $group->timeline->name }}" title="{{ $group->timeline->name }}"></a><a href="{{ url($group->timeline->username) }}"> {{ $group->timeline->name }}</a></td> 
							<td><span class="label label-default">{{$group->type}}</span></td>
							<td>{{ $group->users->count() }}</td> 
							<td>
								<ul class="list-inline">
									<li><a href="{{ url('admin/groups/'.$group->timeline->username.'/edit')}}"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="{{ url('admin/groups/'.$group->id.'/delete')}}" onclick="return confirm('{{ trans("messages.are_you_sure") }}')"><span class="trash-icon bg-danger"><i class="fa fa-trash text-danger" aria-hidden="true"></i></span></a></li>
								</ul>

							</td>
							<td>&nbsp;</td> 
						</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				<div class="pagination-holder">
					{{ $groups->render() }}
				</div>	
			@else
				<div class="alert alert-warning">{{ trans('messages.no_groups') }}</div>
			@endif
		</div>
	</div>
