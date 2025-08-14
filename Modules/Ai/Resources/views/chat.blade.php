@extends('layouts.admin')

@section('page-title')
    {{__('AI Assistant')}}
@endsection

@section('content')
<div class="row" x-data="aiChat()">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{__('AI Assistant')}}</h5>
            </div>
            <div class="card-body">
                {{-- Chat Area --}}
                <div class="chat-area" style="height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                    <template x-for="message in messages">
                        <div class="message" :class="{ 'user-message': message.isUser, 'ai-message': !message.isUser }" style="margin-bottom: 15px;">
                            <div class="message-body" style="padding: 10px 15px; border-radius: 15px; display: inline-block; max-width: 80%;"
                                :style="message.isUser ? 'background-color: #007bff; color: white; float: right; clear: both;' : 'background-color: #f1f0f0; float: left; clear: both;'">
                                <p class="mb-0" x-text="message.text"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input Form --}}
                <form @submit.prevent="sendMessage" class="chat-form">
                    <div class="input-group">
                        <input type="text" x-model="newMessage" class="form-control" placeholder="Type your message here..." :disabled="loading">
                        <button class="btn btn-primary" type="submit" :disabled="loading">
                            <span x-show="!loading">Send</span>
                            <span x-show="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>{{__('My Open Tasks')}}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group" x-show="!taskLoading && tasks.length > 0">
                    <template x-for="task in tasks">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span x-text="task.name"></span>
                            <span class="badge bg-primary rounded-pill" x-text="task.project.project_name"></span>
                        </li>
                    </template>
                </ul>
                <p x-show="!taskLoading && tasks.length === 0">You have no open tasks.</p>
                <div x-show="taskLoading" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function aiChat() {
        return {
            messages: [{ text: 'Hello! How can I help you today?', isUser: false }],
            newMessage: '',
            loading: false,
            tasks: [],
            taskLoading: true,
            init() {
                this.fetchTasks();
            },
            sendMessage() {
                if (this.newMessage.trim() === '') return;

                this.messages.push({ text: this.newMessage, isUser: true });
                const userMessage = this.newMessage;
                this.newMessage = '';
                this.loading = true;

                axios.post('{{ route("api.v1.ai.chat") }}', { message: userMessage })
                    .then(response => {
                        this.messages.push({ text: response.data.reply, isUser: false });
                    })
                    .catch(error => {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (error.response && error.response.data && error.response.data.reply) {
                            errorMessage = error.response.data.reply;
                        } else if (error.response && error.response.data && error.response.data.message) {
                            errorMessage = error.response.data.message;
                        }
                        this.messages.push({ text: errorMessage, isUser: false });
                    })
                    .finally(() => {
                        this.loading = false;
                        // Scroll to bottom of chat
                        this.$nextTick(() => {
                            const chatArea = this.$el.querySelector('.chat-area');
                            chatArea.scrollTop = chatArea.scrollHeight;
                        });
                    });
            },
            fetchTasks() {
                this.taskLoading = true;
                axios.get('/api/v1/me/tasks')
                    .then(response => {
                        this.tasks = response.data;
                    })
                    .catch(error => {
                        console.error('Error fetching tasks:', error);
                        // Optionally show an error message to the user
                    })
                    .finally(() => {
                        this.taskLoading = false;
                    });
            }
        }
    }
</script>
@endsection
