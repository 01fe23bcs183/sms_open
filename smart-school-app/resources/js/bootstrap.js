/**
 * Bootstrap JavaScript Configuration
 * 
 * Prompt 306: Wire CSRF and Session Support for AJAX
 * 
 * Configures Axios and fetch for CSRF token handling and session support.
 */

import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
} else {
    console.warn('CSRF token not found in meta tags');
}

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            console.warn('CSRF token mismatch. Refreshing page...');
            window.location.reload();
        }
        
        if (error.response?.status === 401) {
            console.warn('Session expired. Redirecting to login...');
            window.location.href = '/login';
        }
        
        return Promise.reject(error);
    }
);

window.fetchWithCsrf = async function(url, options = {}) {
    const defaultOptions = {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    };

    if (csrfToken) {
        defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };

    const response = await fetch(url, mergedOptions);

    if (response.status === 419) {
        console.warn('CSRF token mismatch. Refreshing page...');
        window.location.reload();
        return;
    }

    if (response.status === 401) {
        console.warn('Session expired. Redirecting to login...');
        window.location.href = '/login';
        return;
    }

    return response;
};

window.api = {
    get: (url, params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        return window.axios.get(fullUrl);
    },
    
    post: (url, data = {}) => {
        return window.axios.post(url, data);
    },
    
    put: (url, data = {}) => {
        return window.axios.put(url, data);
    },
    
    patch: (url, data = {}) => {
        return window.axios.patch(url, data);
    },
    
    delete: (url, data = {}) => {
        return window.axios.delete(url, { data });
    },
    
    upload: (url, formData, onProgress = null) => {
        return window.axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: onProgress ? (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                onProgress(percentCompleted);
            } : undefined,
        });
    },
};

window.submitFormWithMethod = function(form, method) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_method';
    input.value = method.toUpperCase();
    form.appendChild(input);
    form.submit();
};

window.deleteWithConfirm = async function(url, message = 'Are you sure you want to delete this item?') {
    if (!confirm(message)) {
        return false;
    }
    
    try {
        const response = await window.api.delete(url);
        return response.data;
    } catch (error) {
        console.error('Delete failed:', error);
        throw error;
    }
};
