<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			{{ trans('common.manage_users') }}
		</h3>
	</div>
	<div class="panel-body timeline">
	@include('flash::message')
		@if(count($users) > 0)
			<div class="table-responsive manage-table">
				<table class="table existing-products-table socialite">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th>{{ trans('admin.id') }}</th> 
							<th>{{ trans('auth.name') }}</th>
							<th>{{ trans('common.email') }}</th> 
							<th>{{ trans('admin.options') }}</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@foreach($users as $user)
						<tr>
							<td>&nbsp;</td>	
							<td>{{ $user->id }}</td>
							<td><a href="#"><img src="{{ $user->avatar }}" alt="images"></a><a href="{{ url($user->timeline->username) }}"> {{ $user->timeline->username }}</a></td>

							<td>{{ $user->email }}</td> 
							<td>
								<ul class="list-inline">
									<li><a href="{{ url('admin/users/'.$user->timeline->username.'/edit') }}"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="{{ url('admin/users/'.$user->id.'/delete')}}" onclick="return confirm('{{ trans("messages.are_you_sure") }}')"><span class="trash-icon bg-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></a></li>
								</ul>

							</td>
							<td>&nbsp;</td> 
						</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				<div class="pagination-holder">
					{{ $users->render() }}
				</div>	
			@else
				<div class="alert alert-warning">{{ trans('messages.no_users') }}</div>
			@endif
		</div>
	</div>
