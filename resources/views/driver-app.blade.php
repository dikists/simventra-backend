<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIMVENTRA Driver App</title>
    <meta name="theme-color" content="#1e293b">
    <meta name="description" content="Aplikasi Khusus Sopir Armada SIMVENTRA">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- React 18 & Babel via CDN -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f172a;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }
        #root {
            width: 100%;
            max-width: 430px;
            min-height: 90vh;
            background: #f8fafc;
            border-radius: 36px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5), 0 0 0 10px #1e293b;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        @media (max-width: 500px) {
            body { padding: 0; background: #f8fafc; }
            #root { max-width: 100%; min-height: 100vh; border-radius: 0; box-shadow: none; }
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            text-decoration: none;
        }
        .btn-primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,0.35); }
        .btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; box-shadow: 0 4px 14px rgba(245,158,11,0.35); }
        .btn-danger  { background: #ef4444; color: #fff; }
        .btn:active { transform: scale(0.98); }
        .card {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            margin-bottom: 14px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-teal { background: #ccfbf1; color: #115e59; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .plate-badge {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            padding: 4px 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 1.5px;
            border: 1.5px solid #cbd5e1;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .pulsing-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 1.5s infinite;
            display: inline-block;
        }
    </style>
</head>
<body>

<div id="root"></div>

@verbatim
<script type="text/babel">
const { useState, useEffect, useRef } = React;

// Jakarta - Bandung Highway simulation waypoints
const SIMULATED_ROUTE = [
    { lat: -6.2088, lng: 106.8456, speed: 30 }, // Gudang Jakarta Pusat
    { lat: -6.2255, lng: 106.9011, speed: 55 }, // Tol Cawang
    { lat: -6.2412, lng: 106.9923, speed: 72 }, // Tol Bekasi Barat
    { lat: -6.2625, lng: 107.0850, speed: 80 }, // Tol Cikarang Utama
    { lat: -6.3450, lng: 107.2910, speed: 78 }, // Karawang Timur
    { lat: -6.4912, lng: 107.4410, speed: 65 }, // Tol Cipularang KM 80
    { lat: -6.6850, lng: 107.4520, speed: 70 }, // Cipularang KM 100
    { lat: -6.8520, lng: 107.5410, speed: 60 }, // Padalarang
    { lat: -6.9175, lng: 107.6191, speed: 40 }  // Bandung Logistik Hub
];

function playNotificationSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);

        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }
    } catch (e) {
        console.log('Audio error', e);
    }
}

function DriverApp() {
    // Auth State
    const [token, setToken] = useState(() => localStorage.getItem('simventra_driver_token') || '');
    const [driver, setDriver] = useState(() => {
        const saved = localStorage.getItem('simventra_driver_data');
        return saved ? JSON.parse(saved) : null;
    });

    // Login Form
    const [loginInput, setLoginInput] = useState('bambang.driver@simventra.id');
    const [passwordInput, setPasswordInput] = useState('Password@123');
    const [loginLoading, setLoginLoading] = useState(false);
    const [loginError, setLoginError] = useState('');

    // Task & Assignment State
    const [task, setTask] = useState(null);
    const [hasTask, setHasTask] = useState(false);
    const [taskLoading, setTaskLoading] = useState(false);
    const [actionLoading, setActionLoading] = useState(false);
    const prevTaskStatus = useRef(null);

    // GPS & Tracking State
    const [gpsActive, setGpsActive] = useState(false);
    const [isSimulated, setIsSimulated] = useState(true);
    const [routeIndex, setRouteIndex] = useState(0);
    const [currentCoords, setCurrentCoords] = useState(SIMULATED_ROUTE[0]);
    const [pingCount, setPingCount] = useState(0);
    const [lastPingTime, setLastPingTime] = useState('');

    // Completion Form Modal
    const [showCompleteModal, setShowCompleteModal] = useState(false);
    const [endOdometer, setEndOdometer] = useState('');
    const [returnCondition, setReturnCondition] = useState('baik');
    const [returnNotes, setReturnNotes] = useState('');

    // Login Handler
    const handleLogin = async (e) => {
        if (e) e.preventDefault();
        setLoginLoading(true);
        setLoginError('');
        try {
            const res = await fetch('/api/driver/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ login: loginInput, password: passwordInput })
            });
            const data = await res.json();
            if (data.success) {
                setToken(data.token);
                setDriver(data.driver);
                localStorage.setItem('simventra_driver_token', data.token);
                localStorage.setItem('simventra_driver_data', JSON.stringify(data.driver));
            } else {
                setLoginError(data.message || 'Login gagal.');
            }
        } catch (err) {
            setLoginError('Gagal terhubung ke server.');
        } finally {
            setLoginLoading(false);
        }
    };

    const handleLogout = () => {
        localStorage.removeItem('simventra_driver_token');
        localStorage.removeItem('simventra_driver_data');
        setToken('');
        setDriver(null);
        setTask(null);
        setHasTask(false);
        setGpsActive(false);
    };

    // Fetch Active Task
    const fetchTask = async () => {
        if (!token) return;
        try {
            const res = await fetch('/api/driver/task', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                setHasTask(data.has_task);
                if (data.has_task) {
                    const newTask = data.assignment;
                    // Check if new assignment arrived
                    if (prevTaskStatus.current === null || (prevTaskStatus.current !== newTask.status && newTask.status === 'assigned')) {
                        playNotificationSound();
                    }
                    prevTaskStatus.current = newTask.status;
                    setTask(newTask);
                    if (newTask.status === 'on_trip') {
                        setGpsActive(true);
                    }
                } else {
                    setTask(null);
                    setGpsActive(false);
                    prevTaskStatus.current = null;
                }
            }
        } catch (err) {
            console.error('Error fetching task:', err);
        }
    };

    // Polling task
    useEffect(() => {
        if (token) {
            fetchTask();
            const interval = setInterval(fetchTask, 3500);
            return () => clearInterval(interval);
        }
    }, [token]);

    // Confirm Assignment
    const handleConfirmTask = async () => {
        if (!task) return;
        setActionLoading(true);
        try {
            const res = await fetch(`/api/driver/task/${task.id}/confirm`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                fetchTask();
            }
        } catch (err) {
            alert('Gagal mengonfirmasi tugas.');
        } finally {
            setActionLoading(false);
        }
    };

    // Start Trip
    const handleStartTrip = async () => {
        if (!task) return;
        setActionLoading(true);
        try {
            const res = await fetch(`/api/driver/task/${task.id}/start`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                setGpsActive(true);
                fetchTask();
            }
        } catch (err) {
            alert('Gagal memulai perjalanan.');
        } finally {
            setActionLoading(false);
        }
    };

    // Periodic GPS Dispatch Loop
    useEffect(() => {
        if (!gpsActive || !task) return;

        const sendCoords = async (coords) => {
            try {
                const res = await fetch(`/api/driver/task/${task.id}/location`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: coords.lat,
                        longitude: coords.lng,
                        speed: coords.speed,
                        heading: 120
                    })
                });
                const data = await res.json();
                if (data.success) {
                    setPingCount(prev => prev + 1);
                    setLastPingTime(new Date().toLocaleTimeString());
                }
            } catch (err) {
                console.error('GPS Send Error:', err);
            }
        };

        const interval = setInterval(() => {
            if (isSimulated) {
                setRouteIndex(prev => {
                    const next = (prev + 1) % SIMULATED_ROUTE.length;
                    const nextCoords = SIMULATED_ROUTE[next];
                    setCurrentCoords(nextCoords);
                    sendCoords(nextCoords);
                    return next;
                });
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const coords = {
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            speed: (pos.coords.speed || 0) * 3.6
                        };
                        setCurrentCoords(coords);
                        sendCoords(coords);
                    },
                    (err) => console.log('Geolocation error:', err),
                    { enableHighAccuracy: true }
                );
            }
        }, 4000);

        return () => clearInterval(interval);
    }, [gpsActive, task, isSimulated, token]);

    // Complete Trip
    const handleCompleteTrip = async (e) => {
        e.preventDefault();
        if (!task) return;
        setActionLoading(true);
        try {
            const res = await fetch(`/api/driver/task/${task.id}/complete`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    end_odometer: parseFloat(endOdometer),
                    condition: returnCondition,
                    notes: returnNotes
                })
            });
            const data = await res.json();
            if (data.success) {
                setShowCompleteModal(false);
                setGpsActive(false);
                fetchTask();
                alert('Tugas perjalanan selesai! Armada telah berhasil dilaporkan kembali ke gudang.');
            } else {
                alert(data.message || 'Gagal menyelesaikan tugas.');
            }
        } catch (err) {
            alert('Terjadi kesalahan.');
        } finally {
            setActionLoading(false);
        }
    };

    // -------------------------------------------------------------
    // RENDER: LOGIN SCREEN
    // -------------------------------------------------------------
    if (!token) {
        return (
            <div style={{ padding: '24px', display: 'flex', flexDirection: 'column', justifyContent: 'center', minHeight: '90vh' }}>
                <div style={{ textAlign: 'center', marginBottom: '32px' }}>
                    <div style={{ width: '64px', height: '64px', borderRadius: '18px', background: 'linear-gradient(135deg, #2563eb, #1e40af)', color: '#fff', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '28px', fontWeight: '800', boxShadow: '0 10px 25px -5px rgba(37,99,235,0.4)', marginBottom: '14px' }}>
                        S
                    </div>
                    <h2 style={{ fontSize: '24px', fontWeight: '800', color: '#0f172a' }}>SIMVENTRA Driver</h2>
                    <p style={{ fontSize: '13px', color: '#64748b', marginTop: '4px' }}>Aplikasi Operasional Sopir & GPS Tracker</p>
                </div>

                <form onSubmit={handleLogin} className="card">
                    {loginError && (
                        <div style={{ padding: '10px 12px', background: '#fef2f2', border: '1px solid #fecaca', borderRadius: '10px', color: '#b91c1c', fontSize: '13px', marginBottom: '14px' }}>
                            {loginError}
                        </div>
                    )}

                    <div style={{ marginBottom: '14px' }}>
                        <label style={{ display: 'block', fontSize: '12px', fontWeight: '700', color: '#475569', marginBottom: '6px', textTransform: 'uppercase' }}>
                            Nomor HP / Email Sopir
                        </label>
                        <input
                            type="text"
                            value={loginInput}
                            onChange={e => setLoginInput(e.target.value)}
                            placeholder="0812xxxxxxxx atau email"
                            required
                            style={{ width: '100%', padding: '12px 14px', borderRadius: '12px', border: '1px solid #cbd5e1', fontSize: '15px' }}
                        />
                    </div>

                    <div style={{ marginBottom: '20px' }}>
                        <label style={{ display: 'block', fontSize: '12px', fontWeight: '700', color: '#475569', marginBottom: '6px', textTransform: 'uppercase' }}>
                            Kata Sandi
                        </label>
                        <input
                            type="password"
                            value={passwordInput}
                            onChange={e => setPasswordInput(e.target.value)}
                            placeholder="Kata sandi akun"
                            required
                            style={{ width: '100%', padding: '12px 14px', borderRadius: '12px', border: '1px solid #cbd5e1', fontSize: '15px' }}
                        />
                    </div>

                    <button type="submit" disabled={loginLoading} className="btn btn-primary">
                        {loginLoading ? 'Memeriksa Akun...' : 'Masuk ke Aplikasi'}
                    </button>

                    <div style={{ marginTop: '16px', borderTop: '1px solid #e2e8f0', paddingTop: '14px', textAlign: 'center' }}>
                        <button
                            type="button"
                            onClick={() => { setLoginInput('bambang.driver@simventra.id'); setPasswordInput('Password@123'); }}
                            style={{ background: 'none', border: 'none', color: '#2563eb', fontSize: '12.5px', fontWeight: '600', cursor: 'pointer' }}
                        >
                            ⚡ Klik Di Sini untuk Auto-fill Akun Sopir Demo (Bambang)
                        </button>
                    </div>
                </form>
            </div>
        );
    }

    // -------------------------------------------------------------
    // RENDER: MAIN DRIVER DASHBOARD
    // -------------------------------------------------------------
    return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100%', minHeight: '90vh', background: '#f8fafc' }}>
            {/* Top Bar */}
            <div style={{ background: '#0f172a', color: '#fff', padding: '18px 20px', borderBottomLeftRadius: '24px', borderBottomRightRadius: '24px', boxShadow: '0 4px 20px rgba(0,0,0,0.1)' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div style={{ width: '42px', height: '42px', borderRadius: '50%', background: '#2563eb', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: '800', fontSize: '16px', border: '2px solid rgba(255,255,255,0.2)' }}>
                            {driver ? driver.name.substring(0, 2).toUpperCase() : 'DR'}
                        </div>
                        <div>
                            <div style={{ fontSize: '15px', fontWeight: '700' }}>{driver?.name || 'Sopir'}</div>
                            <div style={{ fontSize: '11px', color: '#94a3b8' }}>{driver?.employee_number} &bull; SIM {driver?.sim_type || 'B1'}</div>
                        </div>
                    </div>
                    <button onClick={handleLogout} style={{ background: 'rgba(255,255,255,0.1)', border: 'none', color: '#fff', padding: '6px 12px', borderRadius: '10px', fontSize: '11.5px', fontWeight: '600', cursor: 'pointer' }}>
                        Keluar
                    </button>
                </div>
            </div>

            {/* Content Body */}
            <div style={{ padding: '16px', flex: 1, overflowY: 'auto' }}>
                {hasTask && task ? (
                    <div>
                        {/* New Assignment Notification Alert */}
                        {task.status === 'assigned' && (
                            <div style={{ padding: '14px', background: '#eff6ff', border: '1.5px solid #3b82f6', borderRadius: '14px', marginBottom: '14px', display: 'flex', alignItems: 'center', gap: '10px' }}>
                                <div style={{ fontSize: '24px' }}>🔔</div>
                                <div style={{ flex: 1 }}>
                                    <div style={{ fontSize: '13px', fontWeight: '800', color: '#1d4ed8' }}>Tugas Baru Masuk!</div>
                                    <div style={{ fontSize: '11.5px', color: '#3b82f6' }}>Koordinator menugaskan armada kepada Anda. Silakan konfirmasi.</div>
                                </div>
                            </div>
                        )}

                        {/* Assignment Details Card */}
                        <div className="card">
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '12px' }}>
                                <span className="plate-badge">{task.vehicle.license_plate}</span>
                                <span className={`badge badge-${task.status === 'on_trip' ? 'purple' : (task.status === 'confirmed' ? 'teal' : 'blue')}`}>
                                    {task.status_label}
                                </span>
                            </div>

                            <div style={{ fontSize: '16px', fontWeight: '800', color: '#0f172a' }}>
                                {task.vehicle.brand} {task.vehicle.model}
                            </div>
                            <div style={{ fontSize: '12px', color: '#64748b', marginTop: '2px' }}>
                                Tipe: {task.vehicle.vehicle_type} &bull; Muatan: {task.vehicle.max_capacity_kg} KG
                            </div>

                            {task.vehicle.is_halal_dedicated && (
                                <div style={{ marginTop: '10px', padding: '6px 10px', background: '#ecfdf5', borderRadius: '8px', fontSize: '11.5px', fontWeight: '700', color: '#047857', display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
                                    ✓ Halal Dedicated Unit
                                </div>
                            )}

                            <div style={{ borderTop: '1px solid #f1f5f9', marginTop: '14px', paddingTop: '12px' }}>
                                <div style={{ fontSize: '11px', color: '#94a3b8', textTransform: 'uppercase', fontWeight: '700' }}>Tujuan Pengiriman</div>
                                <div style={{ fontSize: '14px', fontWeight: '700', color: '#1e293b', marginTop: '2px' }}>
                                    📍 {task.destination || 'Pengiriman Reguler'}
                                </div>
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px', marginTop: '12px' }}>
                                <div style={{ padding: '8px 10px', background: '#f8fafc', borderRadius: '8px' }}>
                                    <div style={{ fontSize: '11px', color: '#94a3b8' }}>KM Awal Berangkat</div>
                                    <div style={{ fontSize: '13px', fontWeight: '700', color: '#0f172a' }}>{task.start_odometer} KM</div>
                                </div>
                                <div style={{ padding: '8px 10px', background: '#f8fafc', borderRadius: '8px' }}>
                                    <div style={{ fontSize: '11px', color: '#94a3b8' }}>Asal Unit</div>
                                    <div style={{ fontSize: '13px', fontWeight: '700', color: '#0f172a' }}>{task.origin}</div>
                                </div>
                            </div>

                            {task.notes && (
                                <div style={{ marginTop: '10px', padding: '8px 10px', background: '#fffbeb', borderRadius: '8px', fontSize: '11.5px', color: '#92400e' }}>
                                    <strong>Instruksi:</strong> {task.notes}
                                </div>
                            )}
                        </div>

                        {/* ACTION BUTTON 1: CONFIRM ASSIGNMENT */}
                        {task.status === 'assigned' && (
                            <button onClick={handleConfirmTask} disabled={actionLoading} className="btn btn-success" style={{ marginBottom: '14px' }}>
                                {actionLoading ? 'Memproses...' : '✓ Konfirmasi Terima Tugas'}
                            </button>
                        )}

                        {/* ACTION BUTTON 2: START TRIP */}
                        {task.status === 'confirmed' && (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', marginBottom: '14px' }}>
                                <div style={{ padding: '12px', background: '#ecfdf5', borderRadius: '12px', fontSize: '12px', color: '#065f46', textAlign: 'center' }}>
                                    Tugas telah dikonfirmasi. Tekan tombol di bawah saat Anda keluar dari gudang untuk mengaktifkan pelacakan GPS otomatis.
                                </div>
                                <button onClick={handleStartTrip} disabled={actionLoading} className="btn btn-primary">
                                    🚀 Mulai Perjalanan (Keluar Gudang)
                                </button>
                            </div>
                        )}

                        {/* ON TRIP: LIVE GPS HUD */}
                        {task.status === 'on_trip' && (
                            <div className="card" style={{ background: '#0f172a', color: '#fff', border: 'none', boxShadow: '0 10px 25px -5px rgba(15,23,42,0.5)' }}>
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                        <span className="pulsing-dot"></span>
                                        <span style={{ fontSize: '12px', fontWeight: '800', letterSpacing: '1px', color: '#10b981' }}>
                                            GPS AKTIF STREAMING
                                        </span>
                                    </div>
                                    <span style={{ fontSize: '11px', color: '#94a3b8' }}>{pingCount} Ping Terkirim</span>
                                </div>

                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', textAlign: 'center', marginBottom: '16px' }}>
                                    <div style={{ background: 'rgba(255,255,255,0.05)', padding: '14px', borderRadius: '14px' }}>
                                        <div style={{ fontSize: '11px', color: '#94a3b8', textTransform: 'uppercase' }}>Kecepatan</div>
                                        <div style={{ fontSize: '28px', fontWeight: '800', color: '#38bdf8', marginTop: '2px' }}>
                                            {Math.round(currentCoords.speed || 0)} <span style={{ fontSize: '12px', color: '#94a3b8' }}>KM/H</span>
                                        </div>
                                    </div>
                                    <div style={{ background: 'rgba(255,255,255,0.05)', padding: '14px', borderRadius: '14px' }}>
                                        <div style={{ fontSize: '11px', color: '#94a3b8', textTransform: 'uppercase' }}>Update Terakhir</div>
                                        <div style={{ fontSize: '14px', fontWeight: '700', color: '#f8fafc', marginTop: '8px' }}>
                                            {lastPingTime || 'Baru saja'}
                                        </div>
                                    </div>
                                </div>

                                <div style={{ fontSize: '11px', color: '#64748b', textAlign: 'center', marginBottom: '14px', fontFamily: 'monospace' }}>
                                    Lat: {currentCoords.lat.toFixed(4)} &bull; Lng: {currentCoords.lng.toFixed(4)}
                                </div>

                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: 'rgba(255,255,255,0.05)', padding: '8px 12px', borderRadius: '10px', marginBottom: '16px', fontSize: '11.5px' }}>
                                    <span>Mode: <strong>{isSimulated ? 'Simulasi Tol Jkt-Bdg' : 'Sensor GPS Nyata'}</strong></span>
                                    <button
                                        onClick={() => setIsSimulated(!isSimulated)}
                                        style={{ background: '#2563eb', border: 'none', color: '#fff', padding: '4px 10px', borderRadius: '6px', fontSize: '11px', cursor: 'pointer' }}
                                    >
                                        Ganti Mode
                                    </button>
                                </div>

                                <button
                                    onClick={() => {
                                        setEndOdometer(task.start_odometer + 150);
                                        setShowCompleteModal(true);
                                    }}
                                    className="btn btn-warning"
                                >
                                    🏁 Selesai & Kembali ke Gudang
                                </button>
                            </div>
                        )}
                    </div>
                ) : (
                    /* Empty State: Standby */
                    <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                        <div style={{ width: '72px', height: '72px', borderRadius: '50%', background: '#e2e8f0', color: '#64748b', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '32px', marginBottom: '16px' }}>
                            ☕
                        </div>
                        <h3 style={{ fontSize: '18px', fontWeight: '800', color: '#1e293b' }}>Standby di Pool / Gudang</h3>
                        <p style={{ fontSize: '13px', color: '#64748b', marginTop: '6px', lineHeight: '1.5' }}>
                            Saat ini belum ada surat tugas armada yang diberikan.<br />
                            Notifikasi lonceng & getar akan berbunyi otomatis saat koordinator menugaskan armada kepada Anda.
                        </p>
                        <div style={{ marginTop: '20px' }}>
                            <button onClick={fetchTask} className="btn btn-primary" style={{ width: 'auto', padding: '10px 20px', fontSize: '13px' }}>
                                Periksa Tugas Baru
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* COMPLETE ASSIGNMENT MODAL */}
            {showCompleteModal && (
                <div style={{ position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.7)', backdropFilter: 'blur(4px)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '16px' }}>
                    <form onSubmit={handleCompleteTrip} className="card" style={{ width: '100%', maxWidth: '380px', margin: 0 }}>
                        <h3 style={{ fontSize: '16px', fontWeight: '800', color: '#0f172a', marginBottom: '4px' }}>Check-in Masuk Gudang</h3>
                        <p style={{ fontSize: '12px', color: '#64748b', marginBottom: '16px' }}>Konfirmasi kepulangan dan lepaskan unit armada.</p>

                        <div style={{ marginBottom: '12px' }}>
                            <label style={{ display: 'block', fontSize: '11px', fontWeight: '700', color: '#475569', textTransform: 'uppercase', marginBottom: '4px' }}>
                                Odometer Akhir Kepulangan (KM) *
                            </label>
                            <input
                                type="number"
                                step="0.1"
                                value={endOdometer}
                                onChange={e => setEndOdometer(e.target.value)}
                                required
                                style={{ width: '100%', padding: '10px 12px', borderRadius: '10px', border: '1px solid #cbd5e1', fontSize: '14px' }}
                            />
                        </div>

                        <div style={{ marginBottom: '12px' }}>
                            <label style={{ display: 'block', fontSize: '11px', fontWeight: '700', color: '#475569', textTransform: 'uppercase', marginBottom: '4px' }}>
                                Kondisi Kendaraan
                            </label>
                            <select
                                value={returnCondition}
                                onChange={e => setReturnCondition(e.target.value)}
                                style={{ width: '100%', padding: '10px 12px', borderRadius: '10px', border: '1px solid #cbd5e1', fontSize: '14px' }}
                            >
                                <option value="baik">Baik & Siap Beroperasi</option>
                                <option value="perlu_cuci">Perlu Dicuci</option>
                                <option value="perlu_perawatan">Perlu Ganti Oli / Servis Ringan</option>
                                <option value="rusak">Ada Kerusakan</option>
                            </select>
                        </div>

                        <div style={{ marginBottom: '18px' }}>
                            <label style={{ display: 'block', fontSize: '11px', fontWeight: '700', color: '#475569', textTransform: 'uppercase', marginBottom: '4px' }}>
                                Catatan Perjalanan (Opsional)
                            </label>
                            <textarea
                                value={returnNotes}
                                onChange={e => setReturnNotes(e.target.value)}
                                rows="2"
                                placeholder="Keterangan pengiriman, sisa BBM, dll..."
                                style={{ width: '100%', padding: '8px 12px', borderRadius: '10px', border: '1px solid #cbd5e1', fontSize: '13px' }}
                            ></textarea>
                        </div>

                        <div style={{ display: 'flex', gap: '8px' }}>
                            <button type="button" onClick={() => setShowCompleteModal(false)} className="btn btn-primary" style={{ background: '#e2e8f0', color: '#334155' }}>
                                Batal
                            </button>
                            <button type="submit" disabled={actionLoading} className="btn btn-warning">
                                {actionLoading ? 'Menyimpan...' : 'Konfirmasi Selesai'}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<DriverApp />);
</script>
@endverbatim

</body>
</html>
