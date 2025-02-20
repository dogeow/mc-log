import React from 'react';
import {UserList} from '../Components/UserList';
import YearlyCalendar from '../Components/YearlyCalendar';
import DailyStats from '../Components/DailyStats';

export default function Dashboard() {
    return (
        <div className="container mx-auto p-4 space-y-8">
            <h1 className="text-2xl font-bold mb-4">Minecraft 服务器统计</h1>
            
            <section>
                <h2 className="text-xl font-bold mb-4">用户列表</h2>
                <UserList />
            </section>
            
            <section>
                <h2 className="text-xl font-bold mb-4">年度活跃度</h2>
                <YearlyCalendar />
            </section>
            
            <section>
                <h2 className="text-xl font-bold mb-4">每日统计</h2>
                <DailyStats />
            </section>
        </div>
    );
} 