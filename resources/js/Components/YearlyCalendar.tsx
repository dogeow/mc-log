import React from 'react';
import axios from 'axios';

interface CalendarData {
    date: string;
    count: number;
}

export default function YearlyCalendar() {
    const [calendarData, setCalendarData] = React.useState<CalendarData[]>([]);

    React.useEffect(() => {
        axios.get('/api/v1/yearly-calendar').then(response => {
            setCalendarData(response.data);
        });
    }, []);

    const getColor = (count: number) => {
        if (count === 0) return 'bg-gray-100';
        if (count <= 2) return 'bg-green-200';
        if (count <= 5) return 'bg-green-400';
        return 'bg-green-600';
    };

    return (
        <div className="grid grid-cols-53 gap-1">
            {calendarData.map(data => (
                <div
                    key={data.date}
                    className={`w-4 h-4 ${getColor(data.count)} rounded`}
                    title={`${data.date}: ${data.count}人在线`}
                />
            ))}
        </div>
    );
} 