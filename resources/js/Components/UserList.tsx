import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface User {
    id: number;
    username: string;
    is_online: boolean;
    total_online_time: number;
    avatar_url: string;
    skin_url: string;
}

export function UserList() {
    const [users, setUsers] = useState<User[]>([]);

    useEffect(() => {
        axios.get('/api/v1/users').then(response => {
            setUsers(response.data);
        });
    }, []);

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {users.map(user => (
                <div key={user.id} className="bg-white rounded-lg shadow p-4">
                    <div className="flex items-center space-x-4">
                        <img 
                            src={user.avatar_url} 
                            alt={user.username} 
                            className="w-12 h-12"
                        />
                        <div>
                            <h3 className="font-bold">{user.username}</h3>
                            <p className={`text-sm ${user.is_online ? 'text-green-500' : 'text-gray-500'}`}>
                                {user.is_online ? '在线' : '离线'}
                            </p>
                            <p className="text-sm text-gray-600">
                                总在线时间: {Math.floor(user.total_online_time / 3600)}小时
                                {Math.floor((user.total_online_time % 3600) / 60)}分钟
                            </p>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
} 