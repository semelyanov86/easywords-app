import { Button } from '@/components/ui/button';

interface FlipButtonProps {
    onClick: () => void;
    isFlipped: boolean;
    label: string;
}

export function FlipButton({ onClick, isFlipped, label }: FlipButtonProps) {
    return (
        <Button
            size="lg"
            onClick={onClick}
            className="group from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 relative overflow-hidden bg-gradient-to-r px-8 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl active:scale-95"
        >
            {/* Animated background */}
            <div className="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-white/0 via-white/20 to-white/0 transition-transform duration-700 group-hover:translate-x-[100%]" />

            {/* Icon with rotation */}
            <span
                className={`mr-2 inline-block transition-transform duration-500 ${isFlipped ? 'rotate-180' : 'rotate-0'} `}
            >
                🔄
            </span>

            {/* Text */}
            <span className="relative z-10">{label}</span>
        </Button>
    );
}
