import { useState, useEffect } from 'react';
import { Check, ChevronsUpDown, X } from "lucide-react";
import { cn } from "@/lib/utils";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from "@/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const CODFunnelBoosterGeoConfig = ({ 
    countries = {},
    sellOption = 'all',
    onSellOptionChange,
    selectedCountries = [],
    onSelectedCountriesChange,
    error 
}) => {
    const [open, setOpen] = useState(false);

    const handleCountrySelect = (countryCode) => {
        const updatedCountries = selectedCountries.includes(countryCode)
            ? selectedCountries.filter(c => c !== countryCode)
            : [...selectedCountries, countryCode];
        onSelectedCountriesChange(updatedCountries);
        setOpen(false);
    };

    return (
        <div className="space-y-6 bg-card/50 dark:bg-card/30 p-6 rounded-xl border border-border/50 dark:border-border/30 backdrop-blur-sm">
            <div className="space-y-4">
                <Label className="text-base font-medium text-foreground">
                    Sales Location Configuration
                </Label>
                <RadioGroup
                    value={sellOption}
                    onValueChange={onSellOptionChange}
                    className="space-y-3"
                >
                    {[
                        { value: 'all', label: 'Sell to all countries' },
                        { value: 'specific', label: 'Sell to specific countries' },
                        { value: 'all_except', label: 'Sell to all countries except' }
                    ].map(({ value, label }) => (
                        <div key={value} className="flex items-center space-x-3">
                            <RadioGroupItem 
                                value={value} 
                                id={value}
                                className="border-input dark:border-input/30"
                            />
                            <Label 
                                htmlFor={value} 
                                className="text-sm font-medium text-foreground cursor-pointer"
                            >
                                {label}
                            </Label>
                        </div>
                    ))}
                </RadioGroup>
            </div>

            {(sellOption === 'specific' || sellOption === 'all_except') && (
                <div className="space-y-4">
                    <Label className="text-sm font-medium text-foreground">
                        {sellOption === 'specific' ? 'Select countries' : 'Exclude countries'}
                    </Label>
                    <div className="space-y-3">
                        <div className="flex flex-wrap gap-2">
                            {selectedCountries.map(countryCode => (
                                <Badge 
                                    key={countryCode} 
                                    variant="secondary" 
                                    className="px-2 py-1 bg-muted/50 dark:bg-muted/20 text-foreground"
                                >
                                    {countries[countryCode] || countryCode}
                                    <button 
                                        onClick={() => handleCountrySelect(countryCode)}
                                        className="ml-2 hover:text-destructive transition-colors"
                                    >
                                        <X className="h-3 w-3" />
                                    </button>
                                </Badge>
                            ))}
                        </div>
                        
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button 
                                    variant="outline" 
                                    className="w-full justify-between h-10 px-3 py-2
                                            bg-background dark:bg-background/5
                                            border-input dark:border-input/20
                                            text-foreground dark:text-foreground
                                            hover:bg-accent/50 dark:hover:bg-accent/10
                                            focus:ring-2 focus:ring-primary/30 dark:focus:ring-primary/20"
                                >
                                    Select countries
                                    <ChevronsUpDown className="h-4 w-4 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent 
                            className="w-[300px] p-0 bg-card dark:bg-card/95 
                                        border border-border/50 dark:border-border/30
                                        shadow-lg backdrop-blur-sm"
                            align="start"
                            >
                                <Command className="rounded-lg">
                                    <CommandInput 
                                        placeholder="Search country..." 
                                        className="h-10 px-3 py-2
                                                    bg-transparent
                                                    border-none focus:ring-0
                                                    text-foreground dark:text-foreground
                                                    placeholder:text-muted-foreground/60"
                                    />
                                    <CommandEmpty className="py-3 px-4 text-sm text-muted-foreground">
                                        No country found.
                                    </CommandEmpty>
                                    <CommandGroup className="max-h-[300px] overflow-y-auto modern-scroll p-1">
                                        {Object.entries(countries).map(([code, country]) => (
                                            <CommandItem
                                                key={code}
                                                onSelect={() => handleCountrySelect(code)}
                                                className="relative flex items-center px-3 py-2.5 rounded-md
                                                            text-sm text-foreground dark:text-foreground
                                                            cursor-pointer select-none outline-none
                                                            hover:bg-accent/40 dark:hover:bg-accent/20
                                                            data-[selected=true]:bg-accent/60 dark:data-[selected=true]:bg-accent/30
                                                            focus:bg-accent/40 dark:focus:bg-accent/20
                                                            transition-colors"
                                            >
                                                <Check
                                                    className={cn(
                                                        "mr-2 h-4 w-4 text-primary dark:text-primary/90",
                                                        selectedCountries.includes(code) 
                                                            ? "opacity-100" 
                                                            : "opacity-0"
                                                    )}
                                                />
                                                <span className="flex-1 truncate">{country}</span>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>
            )}

            {error && (
                <Alert variant="destructive" className="mt-4">
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}
        </div>
    );
};

export default CODFunnelBoosterGeoConfig;