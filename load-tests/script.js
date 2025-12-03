import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 20 }, // Ramp up to 20 users
        { duration: '1m', target: 20 },  // Stay at 20 users
        { duration: '30s', target: 0 },  // Ramp down to 0 users
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    },
};

const BASE_URL = 'http://localhost:8000/api';

export default function () {
    // 1. Public endpoint check
    const res = http.get(`${BASE_URL}/health`);
    check(res, { 'status was 200': (r) => r.status == 200 });

    // 2. Login simulation (if we had credentials)
    // const loginRes = http.post(`${BASE_URL}/login_check`, JSON.stringify({
    //   username: 'admin@school.com',
    //   password: 'password123',
    // }), { headers: { 'Content-Type': 'application/json' } });

    // check(loginRes, { 'login status was 200': (r) => r.status == 200 });

    // const token = loginRes.json('token');

    // 3. Authenticated request
    // const studentsRes = http.get(`${BASE_URL}/students`, {
    //   headers: { Authorization: `Bearer ${token}` },
    // });
    // check(studentsRes, { 'students status was 200': (r) => r.status == 200 });

    sleep(1);
}
