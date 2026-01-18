import { Button } from '@/components/ui/button';

interface FlipButtonProps {
    onClick: () => void;
    isFlipped: boolean;
    label: string;
}

export function FlipButton({ onClick, isFlipped, label }: FlipButtonProps) {
    return (
        <Button
            size="default"
            onClick={onClick}
            className="group border-secondary-700 hover:bg-secondary-700 relative overflow-hidden border-2 bg-secondary px-6 py-5 text-base font-bold text-white shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl active:scale-95 md:px-8 md:py-6 md:text-lg md:hover:scale-110"
        >
            {/* Animated shine effect */}
            <div className="absolute inset-0 translate-x-[-200%] bg-gradient-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-[200%]" />

            {/* Icon with rotation */}
            <span
                className={`mr-2 inline-block text-xl transition-transform duration-500 md:text-2xl ${isFlipped ? 'rotate-180' : 'rotate-0'} `}
            >
                🔄
            </span>

            {/* Text */}
            <span className="relative z-10">{label}</span>
        </Button>
    );
}
