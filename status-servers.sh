
#!/bin/bash

# NewsAggregator Development Servers Status Script
# This script checks the status of both backend and frontend servers

echo "📊 NewsAggregator Servers Status"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if port is in use and get PID
check_port_status() {
    local port=$1
    local name=$2
    local url=$3
    
    local pid=$(lsof -ti :$port 2>/dev/null)
    if [ ! -z "$pid" ]; then
        echo -e "${GREEN}✅ $name: ${NC}Running (PID: $pid)"
        echo -e "   ${BLUE}URL: ${NC}$url"
        
        # Test if it's responding
        if curl -s -o /dev/null -w "%{http_code}" "$url" > /tmp/status_check 2>/dev/null; then
            status_code=$(cat /tmp/status_check)
            if [ "$status_code" -eq 200 ] || [ "$status_code" -eq 404 ]; then
                echo -e "   ${GREEN}Status: ${NC}Responding (HTTP $status_code)"
            else
                echo -e "   ${YELLOW}Status: ${NC}Port open but not responding properly (HTTP $status_code)"
            fi
        else
            echo -e "   ${YELLOW}Status: ${NC}Port open but not responding"
        fi
        rm -f /tmp/status_check
        return 0
    else
        echo -e "${RED}❌ $name: ${NC}Not running"
        echo -e "   ${BLUE}URL: ${NC}$url (unavailable)"
        return 1
    fi
}

# Check Backend
backend_running=$(check_port_status 8000 "Backend API Server" "http://localhost:8000")
echo ""

# Check Frontend  
frontend_running=$(check_port_status 5173 "Frontend Development Server" "http://localhost:5173")
echo ""

# Show PID files status
echo -e "${BLUE}📄 PID Files:${NC}"
if [ -f .backend.pid ]; then
    backend_pid=$(cat .backend.pid)
    if kill -0 $backend_pid 2>/dev/null; then
        echo -e "   Backend PID file: ${GREEN}Valid${NC} ($backend_pid)"
    else
        echo -e "   Backend PID file: ${RED}Stale${NC} ($backend_pid - process not running)"
    fi
else
    echo -e "   Backend PID file: ${YELLOW}Missing${NC}"
fi

if [ -f .frontend.pid ]; then
    frontend_pid=$(cat .frontend.pid)
    if kill -0 $frontend_pid 2>/dev/null; then
        echo -e "   Frontend PID file: ${GREEN}Valid${NC} ($frontend_pid)"
    else
        echo -e "   Frontend PID file: ${RED}Stale${NC} ($frontend_pid - process not running)"
    fi
else
    echo -e "   Frontend PID file: ${YELLOW}Missing${NC}"
fi

echo ""

# Summary
if [ "$backend_running" -eq 0 ] && [ "$frontend_running" -eq 0 ]; then
    echo -e "${GREEN}🎉 All servers are running correctly!${NC}"
    echo ""
    echo -e "${BLUE}Quick Access:${NC}"
    echo -e "   Frontend: ${BLUE}http://localhost:5173${NC}"
    echo -e "   Backend API: ${BLUE}http://localhost:8000${NC}"
    echo -e "   Health Check: ${BLUE}http://localhost:8000/health${NC}"
elif [ "$backend_running" -eq 0 ] || [ "$frontend_running" -eq 0 ]; then
    echo -e "${YELLOW}⚠️  Some servers are running, some are not${NC}"
    echo -e "   Run ${BLUE}./start-servers.sh${NC} to start all servers"
else
    echo -e "${RED}❌ No servers are currently running${NC}"
    echo -e "   Run ${BLUE}./start-servers.sh${NC} to start both servers"
fi

echo ""
echo -e "${YELLOW}💡 Available Commands:${NC}"
echo "   ./start-servers.sh  - Start both servers"
echo "   ./stop-servers.sh   - Stop all servers"
echo "   ./status-servers.sh - Check server status (this command)"

# Show recent log entries if servers are running
if [ "$backend_running" -eq 0 ] && [ -f "backend/storage/logs/server.log" ]; then
    echo ""
    echo -e "${BLUE}📝 Recent Backend Logs (last 5 lines):${NC}"
    tail -n 5 backend/storage/logs/server.log 2>/dev/null || echo "   No recent logs"
fi
