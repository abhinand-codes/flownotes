export const useAuth = () => {
    const token = useCookie('auth_token');
    const user = useState('auth_user');

    const api = $fetch.create({
        baseURL: 'http://localhost:8000/api',
        onRequest({ options }) {
            if (token.value) {
                options.headers = options.headers || {};
                options.headers.Authorization = `Bearer ${token.value}`;
            }
            options.headers = options.headers || {};
            options.headers.Accept = 'application/json';
        },
        onResponseError({ response }) {
            if (response.status === 401) {
                token.value = null;
                user.value = null;
                navigateTo('/login');
            }
        }
    });

    const login = async (form: any) => {
        const data = await api('/login', {
            method: 'POST',
            body: form
        });
        token.value = data.access_token;
        user.value = await api('/me');
    };

    const register = async (form: any) => {
        const data = await api('/register', {
            method: 'POST',
            body: form
        });
        token.value = data.access_token;
        user.value = await api('/me');
    };

    const logout = async () => {
        await api('/logout', { method: 'POST' });
        token.value = null;
        user.value = null;
        navigateTo('/login');
    };

    return {
        api,
        token,
        user,
        login,
        register,
        logout
    };
};
