import React from 'react';
import axios from 'axios';

interface DailyStat {
    username: string;
    online_time: number;
    avatar_url: string;
}

export default function DailyStats() {
    const [date, setDate] = React.useState(new Date().toISOString().split('T')[0]);
    const [stats, setStats] = React.useState<DailyStat[]>([]);

    React.useEffect(() => {
        axios.get(`/api/v1/daily-stats?date=${date}`).then(response => {
            setStats(response.data);
        });
    }, [date]);

    return (
        <div className="space-y-4">
            <input
                type="date"
                value={date}
                onChange={e => setDate(e.target.value)}
                className="border rounded p-2"
            />
            
            <div className="space-y-2">
                {stats.map(stat => (
                    <div key={stat.username} className="flex items-center space-x-4 bg-white p-4 rounded-lg shadow">
                        <img src={stat.avatar_url} alt={stat.username} className="w-8 h-8" />
                        <div>
                            <p className="font-bold">{stat.username}</p>
                            <p className="text-sm text-gray-600">
                                在线时间: {Math.floor(stat.online_time / 3600)}小时
                                {Math.floor((stat.online_time % 3600) / 60)}分钟
                            </p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
} 