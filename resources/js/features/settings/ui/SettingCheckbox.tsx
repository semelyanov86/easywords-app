import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface SettingCheckboxProps {
    id: string;
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    disabled?: boolean;
}

export function SettingCheckbox({
    id,
    label,
    checked,
    onChange,
    disabled,
}: SettingCheckboxProps) {
    return (
        <div className="flex items-start space-x-3 rounded-lg p-3 transition-colors hover:bg-neutral-50">
            <Checkbox
                id={id}
                checked={checked}
                onCheckedChange={(checked) => onChange(checked as boolean)}
                disabled={disabled}
                className="mt-0.5"
            />
            <Label
                htmlFor={id}
                className="flex-1 cursor-pointer text-sm leading-relaxed font-medium text-neutral-700"
            >
                {label}
            </Label>
        </div>
    );
}
