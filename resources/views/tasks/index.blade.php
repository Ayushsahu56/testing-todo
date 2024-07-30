<!DOCTYPE html>
<html>

<head>
    <title>Laravel To-Do List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .toast-confirm {
            text-align: center;
            padding: 0;
            margin: 0;
        }

        .toast-confirm .btn {
            margin: 0 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Laravel To-Do List</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <input type="text" id="task-input" class="form-control flex-grow-1" placeholder="Add Task">
                    <button class="btn btn-primary ml-2 col-md-2" id="add-task">Add Task</button>
                    <button class="btn btn-info ml-2 col-md-2" id="show-all-tasks">Show All Tasks</button>
                </div>
                <table class="table mt-4">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="task-list">
                        <!-- Task rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation Required</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this task?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmDelete" class="btn btn-danger">Yes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let showAll = false; // Initially set to false to show only pending tasks

            function fetchTasks(showAll) {
                $.get('/tasks', {
                    showAll
                }, function(data) {
                    $('#task-list').empty();
                    data.reverse().forEach((task, index) => {
                        $('#task-list').append(`
                    <tr data-id="${task.id}">
                        <td>${index + 1}</td>
                        <td>${task.task}</td>
                        <td>${task.completed ? 'Done' : 'Pending'}</td>
                        <td>
                            ${task.completed ? '' : '<button class="btn btn-success btn-sm complete-task">✓</button>'}
                            <button class="btn btn-danger btn-sm delete-task">✕</button>
                        </td>
                    </tr>
                `);
                    });
                });
            }

            // Fetch only pending tasks initially
            fetchTasks(showAll);

            $('#add-task').click(function() {
                let task = $('#task-input').val();
                if (task) {
                    $.get('/tasks', {
                        task
                    }, function(existingTasks) {
                        let isDuplicate = existingTasks.some(existingTask => existingTask.task ===
                            task);
                        if (isDuplicate) {
                            toastr.warning('Task already exists');
                            return;
                        }
                        $.post('/tasks', {
                            task
                        }, function() {
                            fetchTasks(showAll);
                            $('#task-input').val('');
                            toastr.success('Task added successfully');
                        }).fail(function(response) {
                            toastr.error(response.responseJSON.message);
                        });
                    });
                }
            });

            $('#task-list').on('click', '.complete-task', function() {
                let id = $(this).closest('tr').data('id');
                $.ajax({
                    url: `/tasks/${id}`,
                    type: 'PATCH',
                    success: function() {
                        fetchTasks(showAll);
                        toastr.success('Task marked as completed');
                    }
                });
            });

            $('#task-list').on('click', '.delete-task', function() {
                let id = $(this).closest('tr').data('id');

                $('#confirmationModal').modal('show');

                $('#confirmDelete').off('click').on('click', function() {
                    $.ajax({
                        url: `/tasks/${id}`,
                        type: 'DELETE',
                        success: function() {
                            fetchTasks(showAll);
                            $('#confirmationModal').modal('hide');
                            toastr.success('Task deleted successfully');
                        }
                    });
                });
            });

            $('#show-all-tasks').click(function() {
                showAll = !showAll; // Toggle the state
                $(this).text(showAll ? 'Show Less Tasks' : 'Show All Tasks');
                fetchTasks(showAll);
            });
        });
    </script>
</body>

</html>
