<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Add Quesions and Answers</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
</head>

<body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#!">Q&A</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ '/' }}">Add</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('list-questions') }}">Edit</a></li>

                </ul>
            </div>
        </div>
    </nav>
    <!-- Page header with logo and tagline-->
    <header class="py-5 bg-light border-bottom mb-4">
        <div class="container">
            <div class="text-center my-5">
                <h1 class="fw-bolder">Edit Q&A!</h1>

            </div>
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </header>
    <!-- Page content-->
    <div class="container">
        <div class="row">
            <!-- Blog entries-->
            <div class="col-lg-8">
                <!-- Featured blog post-->
                <div>
                    <form method="POST" action="{{ url('store/edit-questions') }}">
                        @csrf
                        <div class="form-group">
                            <label for="question">Question:</label>
                            <input type="text" name="question" value="{{ $question->questions }}"
                                class="form-control" id="question" placeholder="Enter question">
                            <input type="hidden" name="q_id" value="{{ $question->id }}">
                        </div>

                        <div class="form-group">
                            <label for="corresponding_number">Question Number:</label>
                            <input type="text" name="corresponding_number"
                                value="{{ $question->corresponding_number }}" class="form-control"
                                id="corresponding_number" placeholder="Enter question corresponding number">
                        </div>


                        <div class="form-group">
                            <label for="cat">Category:</label>
                            <select name="category" id="cat" class="form-control">
                                <option value="{{ $question->category ?? '' }}" selected>
                                    {{ $question->category ?? '' }}</option>
                                <option value="hypertension">Hypertension</option>
                                <option value="diabetes">Diabetes</option>
                            </select>

                        </div>
                        <div class="form-group">
                            <label for="sub_cat">sub Category:</label>
                            <select name="sub_category" id="sub_cat" class="form-control">
                                <option value="{{ $question->sub_category ?? '' }}" selected>
                                    {{ $question->sub_category ?? '' }}</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>


                            </select>

                        </div>

                        <div class="form-group">
                            <label for="answer">Answer:</label>
                            <textarea name="answer" class="form-control" id="exampleTextarea" rows="3" placeholder="Enter answer">{{ $answer->answers }}</textarea>
                            <input type="hidden" name="a_id" value="{{ $answer->id }}">

                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>

                </div>
               
                <!-- Nested row for non-featured blog posts-->


            </div>
            <div class="col">
                 <!-- Add this form below your existing submit button for deletion -->
                 <form method="POST" action="{{ url('delete-question/' . $question->id) }}"
                    onsubmit="return confirm('Are you sure you want to delete this question?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
            <!-- Side widgets-->

        </div>
    </div>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('js/scripts.js') }}"></script>
</body>

</html>
