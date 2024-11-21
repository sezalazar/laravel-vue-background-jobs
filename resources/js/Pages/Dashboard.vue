<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';

const backgroundJobs = ref([]);
const logs = ref([]);
const selectedJob = ref(null);
const showModal = ref(false);
const jobForm = reactive({
    className: '',
    methodName: '',
});
const methodParamsValues = reactive({});
const showJobForm = ref(false);
const allowedClasses = ref([]);
const classMethods = ref([]);
const methodParameters = ref([]);

function showCatchError() {
    alert("Something went wrong. Please try again or contact support.");
}

async function getBackgroundJobs() {
    try {
        const response = await axios.get('/background-jobs');
        backgroundJobs.value = response.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } catch {
        showCatchError();
    }
}

async function getAllowedClasses() {
    try {
        const response = await axios.get('/background-jobs/allowed-classes');
        allowedClasses.value = Object.keys(response.data).map(key => ({
            name: key,
            ...response.data[key]
        }));
    } catch {
        showCatchError();
    }
}

async function getClassMethods(className) {
    try {
        const response = await axios.post('/background-jobs/class-methods', { className });
        classMethods.value = response.data;
    } catch {
        showCatchError();
        classMethods.value = [];
    }
}

async function getMethodParameters(className, methodName) {
    try {
        const response = await axios.post('/background-jobs/method-parameters', { className, methodName });
        methodParameters.value = response.data;
        methodParamsValues.value = {};
        methodParameters.value.forEach(param => {
            methodParamsValues[param.name] = param.default || '';
        });
    } catch {
        showCatchError();
        methodParameters.value = [];
        methodParamsValues.value = {};
    }
}

watch(() => jobForm.className, (newClass) => {
    if (newClass) {
        getClassMethods(newClass);
    } else {
        classMethods.value = [];
    }
    jobForm.methodName = '';
    methodParameters.value = [];
    methodParamsValues.value = {};
});

watch(() => jobForm.methodName, (newMethod) => {
    if (newMethod && jobForm.className) {
        getMethodParameters(jobForm.className, newMethod);
    } else {
        methodParameters.value = [];
        methodParamsValues.value = {};
    }
});

async function getLogs(jobId) {
    try {
        const response = await axios.get(`/background-jobs/${jobId}/logs`);
        logs.value = response.data;
        selectedJob.value = backgroundJobs.value.find(job => job.id === jobId);
        showModal.value = true;
    } catch {
        showCatchError();
    }
}

async function cancelJob(jobId) {
    if (confirm('Cancel this job?')) {
        try {
            await axios.post(`/background-jobs/${jobId}/cancelBackgroundJob`);
            alert('Job cancelled successfully.');
            getBackgroundJobs();
        } catch {
            showCatchError();
        }
    }
}

async function retryJob(jobId) {
    if (confirm('Retry this job?')) {
        try {
            await axios.post(`/background-jobs/${jobId}/retry`);
            alert('The job has started processing again.');
            getBackgroundJobs();
        } catch {
            showCatchError();
        }
    }
}

async function createJob() {
    try {
        const response = await axios.post('/background-jobs/runBackgroundJob', {
            className: jobForm.className,
            methodName: jobForm.methodName,
            params: methodParamsValues,
        });

        alert('Job created successfully.');
        backgroundJobs.value.unshift(response.data.job);

        jobForm.className = '';
        jobForm.methodName = '';
        methodParameters.value = [];
        methodParamsValues.value = {};
        showJobForm.value = false;
    } catch {
        showCatchError();
    }
}

onMounted(() => {
    getBackgroundJobs();
    getAllowedClasses();
});
</script>

<template>
    <Head title="Process Monitor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Process Monitor</h2>
        </template>

        <div class="container mx-auto p-6">
            <h1 class="text-2xl font-bold mb-4">Background Process Monitor</h1>

            <div class="mb-6">
                <button
                    @click="showJobForm = !showJobForm"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition"
                >
                    {{ showJobForm ? 'Hide Form' : 'Create New Job' }}
                </button>

                <div v-if="showJobForm" class="mt-4 p-4 border border-gray-300 rounded bg-gray-50">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Class Name</label>
                    <select
                        v-model="jobForm.className"
                        class="w-full p-2 border border-gray-300 rounded mb-4"
                    >
                        <option value="" disabled>Select a class</option>
                        <option v-for="classOption in allowedClasses" :key="classOption.name" :value="classOption.name">
                            {{ classOption.name }}
                        </option>
                    </select>

                    <label class="block mb-2 text-sm font-medium text-gray-700">Method Name</label>
                    <select
                        v-model="jobForm.methodName"
                        class="w-full p-2 border border-gray-300 rounded mb-4"
                        :disabled="classMethods.length === 0"
                    >
                        <option value="" disabled>Select a method</option>
                        <option v-for="method in classMethods" :key="method" :value="method">
                            {{ method }}
                        </option>
                    </select>

                    <div v-if="methodParameters.length > 0" class="mt-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Parameters</label>
                        <div v-for="param in methodParameters" :key="param.name" class="mb-4">
                            <label :for="param.name" class="block text-sm font-medium text-gray-700">
                                {{ param.name }} <span v-if="!param.optional" class="text-red-500">*</span>
                            </label>
                            <input
                                :id="param.name"
                                v-model="methodParamsValues[param.name]"
                                :placeholder="'Enter ' + param.type"
                                type="text"
                                class="w-full p-2 border border-gray-300 rounded"
                            />
                        </div>
                    </div>

                    <button
                        @click="createJob"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition"
                    >
                        Submit
                    </button>
                </div>
            </div>

            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700">Class</th>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700">Method</th>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700">Status</th>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700">Retries</th>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700">Date</th>
                        <th class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="job in backgroundJobs" :key="job.id" class="border-b hover:bg-gray-50">
                        <td class="border border-gray-300 px-6 py-2 text-sm text-gray-600">{{ job.class }}</td>
                        <td class="border border-gray-300 px-6 py-2 text-sm text-gray-600">{{ job.method }}</td>
                        <td
                            class="border border-gray-300 px-6 py-2 text-sm capitalize flex items-center gap-2"
                            :class="{
                                'text-red-500': job.status === 'failed' || job.status === 'cancelled',
                                'text-green-500': job.status === 'completed',
                            }"
                        >
                            <img v-if="job.status === 'running'" src="/images/icon-executing.png" alt="Running Icon" class="w-5 h-5" />
                            {{ job.status }}
                        </td>
                        <td class="border border-gray-300 px-6 py-2 text-sm text-gray-600">{{ job.retry_count }} / {{ job.max_retries }}</td>
                        <td class="border border-gray-300 px-6 py-2 text-sm text-gray-600">{{ new Date(job.created_at).toLocaleString() }}</td>
                        <td class="border border-gray-300 px-6 py-2 text-sm text-gray-600 text-center">
                            <div class="flex justify-center items-center gap-2">
                                <img
                                    v-if="job.status === 'running'"
                                    src="/images/icon-cancel.png"
                                    alt="Cancel"
                                    class="w-5 h-5 cursor-pointer"
                                    @click="cancelJob(job.id)"
                                    title="Cancel Job"
                                />
                                <img
                                    v-if="job.status !== 'pending'"
                                    src="/images/icon-log.png"
                                    alt="View Logs"
                                    class="w-5 h-5 cursor-pointer"
                                    @click="getLogs(job.id)"
                                    title="View Logs"
                                />
                                <img
                                    v-if="job.status === 'failed'"
                                    src="/images/icon-retry.png"
                                    alt="Retry"
                                    class="w-5 h-5 cursor-pointer"
                                    @click="retryJob(job.id)"
                                    title="Retry Job"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white rounded-lg p-6 w-2/3">
                <h2 class="text-lg font-bold mb-4">Logs for {{ selectedJob?.class }}::{{ selectedJob?.method }}</h2>
                <ul>
                    <li v-for="log in logs" :key="log.id" class="mb-2">
                        <span class="text-sm text-gray-600">{{ log.created_at }}:</span>
                        <span class="text-sm text-gray-800">{{ log.message }}</span>
                    </li>
                </ul>
                <button @click="showModal = false" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
