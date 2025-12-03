import { lazy, Suspense } from 'react'
import { createBrowserRouter } from 'react-router-dom'
import Layout from '@/components/Layout'
import LoadingSpinner from '@/components/ui/LoadingSpinner'

// Lazy load pages
const Dashboard = lazy(() => import('@/pages/Dashboard'))
const Students = lazy(() => import('@/pages/Students'))
const Teachers = lazy(() => import('@/pages/Teachers'))
const Settings = lazy(() => import('@/pages/Settings'))
const Login = lazy(() => import('@/pages/Login'))
const NotFound = lazy(() => import('@/pages/NotFound'))

export const router = createBrowserRouter([
    {
        path: '/',
        element: <Layout />,
        errorElement: <NotFound />,
        children: [
            {
                index: true,
                element: (
                    <Suspense fallback={<LoadingSpinner />}>
                        <Dashboard />
                    </Suspense>
                ),
            },
            {
                path: 'students',
                element: (
                    <Suspense fallback={<LoadingSpinner />}>
                        <Students />
                    </Suspense>
                ),
            },
            {
                path: 'teachers',
                element: (
                    <Suspense fallback={<LoadingSpinner />}>
                        <Teachers />
                    </Suspense>
                ),
            },
            {
                path: 'settings',
                element: (
                    <Suspense fallback={<LoadingSpinner />}>
                        <Settings />
                    </Suspense>
                ),
            },
        ],
    },
    {
        path: '/login',
        element: (
            <Suspense fallback={<LoadingSpinner />}>
                <Login />
            </Suspense>
        ),
    },
])
